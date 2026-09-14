# Design: page layouts

**Status:** approved, not yet implemented
**Date:** 2026-09-14

## Context

The library has 44 components but no notion of a *page*. Every application still
hand-writes the document shell — doctype, `<head>`, Rivet's asset tags, the header, the
layout wrapper, the footer — and every application writes it slightly differently. That
boilerplate is exactly what a developer should not be thinking about.

Rivet publishes this as its **blank page** layout, in several variants. This adds a `Page`
component that assembles the whole document, so a page template carries only what is
specific to that page.

## Decisions

| Decision | Choice |
|---|---|
| Shape | A component with registering slots, **not** template inheritance |
| Document scope | Full document, `<!doctype>` through `</html>` |
| Rivet asset tags | Emitted by the layout, defaulted on, switchable off |
| App-wide config | A `PageDefaults` value object, configured once |
| Sidebar content | A slot the page fills |
| Id numbering | Opening a page resets the render context |
| Variants in v1 | All three: single column, sidebar, anchored sidebar |

### Why a component rather than `{% extends %}`

Template inheritance needs the layout to be a file the engine can load. Twig can load one
from a vendor package through a namespaced loader path. **Latte cannot** — its `FileLoader`
throws for any template outside the application's own `templates/` directory. That is the
same constraint that pushed all markup generation into PHP in the first place.

The alternatives were considered and rejected:

- **Ship layout templates and wire the loaders.** Requires writing a custom Latte loader,
  and maintaining two hand-written layout files that must stay in step — the drift this
  library exists to avoid.
- **Scaffold a starter layout into the application.** Gives native inheritance and the app
  owns the result, but there is then no upgrade path when Rivet's layout changes.

A component works identically in both engines with no configuration, and the
registering-slot mechanism it needs is already proven here by `Tabs`/`Tab`.

## The three layouts

Markup below was read from Rivet's published previews at
`https://rivet.iu.edu/layouts/preview/blank-page/{variant}/`, not reconstructed. The
`rvt-layout*` classes were confirmed present in `rivet-core@2.9.1`'s stylesheet.

They are **structurally different documents**, not one structure with modifiers.

| | `<main>` is | Sidebar | Breadcrumbs + `<h1>` |
|---|---|---|---|
| `SingleColumn` | flex column | — | shaded full-bleed band above the wrapper |
| `Sidebar` | flex column | inside `rvt-container-lg`, no background | same shaded band |
| `AnchoredSidebar` | **the wrapper itself** | flush to the viewport edge, `rvt-bg-black-000` | **no band** — inside the content, in a `rvt-prose` block |

### SingleColumn

```html
<body class="rvt-layout">
  {header}
  <main id="main-content" class="rvt-flex rvt-flex-column rvt-grow-1">
    <div class="rvt-bg-black-000 rvt-border-bottom rvt-p-top-xl">
      <div class="rvt-container-lg rvt-prose rvt-flow rvt-p-bottom-xl">
        {breadcrumbs}<h1 class="rvt-m-top-xs">{heading}</h1>
      </div>
    </div>
    <div class="rvt-layout__wrapper [ rvt-p-tb-xxl ]">
      <div class="rvt-container-lg">{content}</div>
    </div>
  </main>
  {footer}
</body>
```

### Sidebar

Identical to `SingleColumn` above the wrapper; the wrapper carries the container and holds
two regions.

```html
    <div class="rvt-layout__wrapper rvt-layout__wrapper--details rvt-container-lg">
      <div class="rvt-layout__sidebar [ rvt-p-top-xxl rvt-flow rvt-prose ]" id="section-nav">{sidebar}</div>
      <div class="rvt-layout__content [ rvt-p-top-xxl ]">{content}</div>
    </div>
```

### AnchoredSidebar

`<main>` becomes the wrapper, so there is nowhere above it for a full-bleed band. The
container moves inside the content region, and the heading goes with it.

```html
  <main id="main-content" class="rvt-layout__wrapper rvt-layout__wrapper--details">
    <div class="rvt-layout__sidebar [ rvt-p-top-xxl rvt-p-left-md rvt-bg-black-000 ]" id="section-nav">{sidebar}</div>
    <div class="rvt-layout__content [ rvt-p-top-xxl rvt-p-lr-md rvt-p-lr-xxl-md-up ]">
      <div class="rvt-container-lg rvt-m-top-xl rvt-m-left-none rvt-m-right-none rvt-p-right-none rvt-p-left-none">
        <div class="rvt-prose">{breadcrumbs}<h1 class="rvt-m-top-xs">{heading}</h1></div>
        {content}
      </div>
    </div>
  </main>
```

**`rvt-layout__wrapper--single` is deliberately unused.** It exists in the stylesheet, but
none of Rivet's published blank-page examples use it; the single-column case omits the
sidebar and content divs entirely rather than modifying the wrapper.

## Architecture

### `Guild\Rivet\Page\PageDefaults`

Immutable, configured once per application, holding everything identical on every page.

```php
new PageDefaults(
    appTitle: 'Course Catalog',          // required; header lockup and <title> suffix
    appSubtitle: 'Indiana University',
    homeHref: '/',
    navItems: [...],                     // header nav tree
    searchAction: null,
    footerLinks: [],                     // extra links; IU's required ones are built into Footer
    footerLight: false,
    containerSize: ContainerSize::Large,
    lang: 'en',
    titleSeparator: ' · ',
    description: null,                   // default meta description
    assets: new RivetAssets(),
);
```

Only the fields applications actually vary are exposed. `Footer` already defaults IU's
required accessibility, privacy and copyright links, so those are not repeated here.

### `Guild\Rivet\Page\RivetAssets`

Separate so `PageDefaults` stays readable, and so a self-hosted application can point at
its own files rather than disabling the feature.

```php
new RivetAssets(
    enabled: true,
    icons: true,
    version: Rivet::VERSION,
    iconsVersion: '3.0.1',
    cssHref: null,        // null → CDN URL built from version
    jsSrc: null,
    iconsCssHref: null,
    iconsJsSrc: null,
);
```

### `Guild\Rivet\Component\Page\Page` — `rvt_page`

Parameters: `title`, `heading`, `description`, `layout` (`PageLayout`, default
`SingleColumn`).

```php
enum PageLayout: string {
    case SingleColumn = 'single_column';
    case Sidebar = 'sidebar';
    case AnchoredSidebar = 'anchored_sidebar';
}
```

Resolution rules, stated so they cannot be read two ways:

- **`<title>`** is `{title}{titleSeparator}{appTitle}`, or just `appTitle` when the page
  sets no title.
- **`description`** on the page overrides the one on `PageDefaults`; neither set means no
  meta tag at all.
- **The heading band** (`SingleColumn`, `Sidebar`) renders only when there is a heading or
  registered breadcrumbs, and the `<h1>` only when there is a heading. Under
  `AnchoredSidebar` the same content appears in a `rvt-prose` block inside the content
  region, on the same condition.
- **`id="section-nav"`** on the sidebar is a literal, not derived. There is one sidebar per
  document, so it cannot collide with itself, and Rivet's examples use that exact value.

Assembles the document from `PageDefaults`, its own parameters, the captured body, and
whatever the slots registered. Branches on `layout` for the three structures above.

### Slots

Each renders nothing in place and registers its content with the enclosing page, so it can
be written anywhere in the body and still land in the right part of the document — the
`Tabs`/`Tab` mechanism.

| Slot | Lands in |
|---|---|
| `rvt_page_styles` | `<head>`, after Rivet's stylesheet |
| `rvt_page_scripts` | before `</body>`, after Rivet's script |
| `rvt_page_sidebar` | the sidebar region |
| `rvt_page_breadcrumbs` | the heading band, or the content block for `AnchoredSidebar` |

Anything **not** in a slot is the main content.

### `PageDefaults` reaches the page through `RenderContext`

`RenderContext` already carries render-scoped services — the id generator. `PageDefaults`
is render-scoped configuration and joins it there. `Renderer` takes one and preserves it
across `reset()`.

### `Guild\Rivet\Render\StartsRender`

A marker interface. `Renderer::open()` resets the context before pushing a component that
implements it. This is the only point early enough: a page's children render *before* the
page itself, so a reset performed during `Page::render()` would come too late.

`Page` implements it. The effect is that any page render is reproducible — the same page
always emits the same identifiers — and the `Renderer::reset()` call the integration
currently has to remember becomes unnecessary for pages.

## API

```twig
{% rvt_page title="Chemistry" heading="Chemistry" layout="sidebar" %}
  {% rvt_page_breadcrumbs %}{{ rvt_breadcrumbs(items: [...]) }}{% endrvt_page_breadcrumbs %}
  {% rvt_page_sidebar %}{{ rvt_sidenav(label: 'Programs', items: [...]) }}{% endrvt_page_sidebar %}

  <p>Page content.</p>

  {% rvt_page_styles %}<link rel="stylesheet" href="/css/chem.css">{% endrvt_page_styles %}
  {% rvt_page_scripts %}<script src="/js/chem.js"></script>{% endrvt_page_scripts %}
{% endrvt_page %}
```

```latte
{rvtPage title: 'Chemistry', heading: 'Chemistry', layout: 'sidebar'}
  {rvtPageBreadcrumbs}{rvtBreadcrumbs items: [...]}{/rvtPageBreadcrumbs}
  {rvtPageSidebar}{rvtSidenav label: 'Programs', items: [...]}{/rvtPageSidebar}

  <p>Page content.</p>

  {rvtPageStyles}<link rel="stylesheet" href="/css/chem.css">{/rvtPageStyles}
  {rvtPageScripts}<script src="/js/chem.js"></script>{/rvtPageScripts}
{/rvtPage}
```

### Framework integration

`ApplicationBuilder::addRivet(?PageDefaults $pageDefaults = null)`. Passed explicitly
rather than read from a config file: an *optional* config file is not a pattern
`guild/framework` has, and inventing one for this is not worth it. An application that
wants its defaults in a file can `require` it at the call site.

## Error cases

| When | Result |
|---|---|
| `rvt_page` with no `PageDefaults` configured | throws, naming `addRivet()` and the `Renderer` constructor as the fix |
| any `rvt_page_*` slot outside a page | `ComponentContextException`, the existing mechanism, caught at compile time by both engines |
| a page nested inside another page | throws — a page is a document, and the inner reset would silently renumber the outer one's identifiers |
| `rvt_page_sidebar` filled under `layout: single_column` | throws rather than silently dropping the content |

## Testing

- **Unit**, per region: head composition and title assembly, asset toggles, each of the
  three layout structures, slot routing, the heading band appearing only when there is a
  heading or breadcrumbs.
- **Context**, for every error above.
- **Parity**: a full page in each of the three layouts added to `CrossEngineParityTest` —
  the real proof both engines assemble the document identically.
- **Demo**: rewrite `examples/` to use the layout. It currently hand-writes the doctype,
  head and asset tags in `examples/public/index.php`; converting it dogfoods the layout and
  should visibly shrink the file.

## Documentation

`README.md` currently states that asset delivery is out of scope and the application
supplies Rivet. That is now only true when the layout is not used, or when assets are
switched off. **Rewrite that section** rather than leaving two statements that disagree.

## Out of scope

- Rivet's other twelve layouts (app index, details, landing, profile, and so on). The
  component takes a `PageLayout`, so they are additive later.
- `rvt-layout__break-out` and `rvt-layout__feature-slot`. Real Rivet classes, but content
  concerns rather than page structure; a page can use them directly.
- The marketing blank-page variants. Application layouts first.

## Risks

- **Rivet 3 rewrites Header and Footer**, both of which the page composes. The page's own
  structure is `rvt-layout*`, which v3 keeps, so the blast radius is the two components it
  embeds rather than the layout itself.
- **The asset decision is now stated in two places** — `RivetAssets` defaults and the
  README. They will drift unless the README points at the value object rather than
  restating version numbers.
