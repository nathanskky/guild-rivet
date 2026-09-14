# guild/rivet

Indiana University [Rivet Design System](https://rivet.iu.edu) components for PHP, with
native Twig and Latte integrations.

Write `{% rvt_dialog title="Confirm" %}` instead of forty lines of markup copied from
rivet.iu.edu — and get the identifier wiring, ARIA attributes and accessible names right
without having to remember them.

Targets **Rivet 2.9.1**. The library emits markup only and ships no JavaScript of its
own; Rivet's own script drives every interactive component.

## Requirements

- PHP `~8.5.0`
- [`twig/twig`](https://twig.symfony.com) `^3.28` or [`latte/latte`](https://latte.nette.org) `^3.1`,
  if you want the template integration. Neither is required to use the components from PHP.

## Installation

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/nathanskky/guild-rivet.git"
        }
    ]
}
```

```bash
composer require guild/rivet
```

### Rivet's own assets

A page built with `rvt_page` emits Rivet's stylesheet and script by default, including the
separate icons package `rvt_icon` needs — nothing to add yourself.

An application that does not use `rvt_page`, or that turns the tags off on `PageDefaults`,
supplies Rivet itself:

```html
<link rel="stylesheet" href="https://unpkg.com/rivet-core@2.9.1/css/rivet.min.css">
<script src="https://unpkg.com/rivet-core@2.9.1/js/rivet.min.js"></script>
<script>Rivet.init()</script>
```

`Rivet.init()` installs a MutationObserver, so components injected later — by htmx, Turbo
or anything else — initialise themselves.

The `rvt_icon` component additionally needs the separate icons package:

```html
<link rel="stylesheet" href="https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icon-element.css">
<script type="module" src="https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icons.js"></script>
```

Components never depend on it for their own chrome — a dialog's close button works with
only the core CSS and JS loaded.

An application using `rvt_page` pins a different version, self-hosts either file, or drops
one by configuring `Guild\Rivet\Page\RivetAssets` on `PageDefaults` — it and
`Rivet::VERSION` are the only places a release is named. `RivetAssets` is read only through
`PageDefaults`, so it has no effect on the hand-written snippets above: an application
supplying Rivet by hand makes the same changes by editing those URLs directly.

## Setup

### Guild framework

One call, after the template engine:

```php
return Application::configure($basePath)
    ->addRouting()
    ->addTemplateEngine(TemplateEngine::Twig)
    ->addRivet()
    ->enableAutoWiring()
    ->create();
```

`addRivet()` reads which engine was chosen, so it must come after
`addTemplateEngine()`; it throws `ConfigurationException` if it does not. Nothing else is
needed — no config file, and no reset call, because the framework builds its container
per request.

The two sections below are for wiring the components into an engine yourself.

### Twig

```php
use Guild\Rivet\Rivet;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Twig\{RivetExtension, RivetRuntime};
use Twig\RuntimeLoader\FactoryRuntimeLoader;

$registry = Rivet::registry();
$renderer = new Renderer($registry);

$twig->addExtension(new RivetExtension($registry));
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
]));
```

### Latte

```php
use Guild\Rivet\Rivet;
use Guild\Rivet\Latte\RivetExtension;
use Guild\Rivet\Render\Renderer;

$registry = Rivet::registry();

$latte->addExtension(new RivetExtension($registry, new Renderer($registry)));
```

### Starting each page

```php
$renderer->reset();
```

Call this once per request. Without it identifiers stay unique but keep climbing for the
life of the process, so the same page will not render identically twice. Neither engine
offers a reliable hook for this, so the integration has to say when a page begins.

Rendering `rvt_page` resets the context itself, so this call is only needed when rendering
components directly, outside the layout below.

## Page layout

`rvt_page` assembles a whole document — doctype through `</html>` — so a page template
carries only what is specific to that page: the header, the layout structure, the footer
and Rivet's asset tags all come from it.

### `PageDefaults`

Configured once per application and passed to the `Renderer` constructor (or to
`addRivet()` in a Guild application), holding everything that is identical on every page:

```php
new PageDefaults(
    appTitle: 'Course Catalog',          // required; header lockup and <title> suffix
    appSubtitle: 'Indiana University',
    homeHref: '/',
    navItems: [...],                     // header nav tree, as rvt_header accepts
    searchAction: null,
    footerLinks: [],                     // extra links; IU's required ones are built into rvt_footer
    footerLight: false,
    containerSize: ContainerSize::Large,
    lang: 'en',
    titleSeparator: ' · ',
    description: null,                   // default meta description
    assets: new RivetAssets(),
);
```

Rendering `rvt_page` without one throws `Exception\ConfigurationException`, naming the fix.

### Layouts

`layout` takes one of three `PageLayout` values, each a structurally different document
rather than one structure with modifiers:

| Value | Sidebar | Heading and breadcrumbs |
|---|---|---|
| `single_column` (default) | none | shaded, full-bleed band above the content |
| `sidebar` | inside the container, no background | same shaded band |
| `anchored_sidebar` | flush to the viewport edge | inside the content, no band |

The heading band (or, under `anchored_sidebar`, the content block in its place) appears
only when the page sets a heading or has registered breadcrumbs. Filling
`rvt_page_sidebar` under `single_column` throws — that layout has nowhere for it to go.

### Slots

Each slot renders nothing in place; it registers its content with the enclosing page, so
it can be written anywhere in the body and still land in the right part of the document.
Anything not inside one of these four is the main content:

| Slot | Lands in |
|---|---|
| `rvt_page_styles` | `<head>`, after Rivet's stylesheet |
| `rvt_page_scripts` | before `</body>`, after Rivet's script |
| `rvt_page_sidebar` | the sidebar region |
| `rvt_page_breadcrumbs` | the heading band, or the content block under `anchored_sidebar` |

### Example

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

## Usage

Components are **tags** in both engines — snake_case in Twig, camelCase in Latte.

- A component that takes **no body** is a single tag: `{% rvt_badge text="New" %}` /
  `{rvtBadge text: 'New'}`.
- A component that **takes a body** is a paired tag: `{% rvt_card %}…{% endrvt_card %}` /
  `{rvtCard}…{/rvtCard}`.
- Every component is also a Twig **function**, and a component that takes a body has
  Latte's self-closing `{tag /}` as the equivalent short form. Both read better when a
  body would only be a label: `{{ rvt_button('Save') }}` / `{rvtButton 'Save' /}`.

`composer components` prints which is which.

```twig
{{ rvt_button('Save changes', purpose: 'danger') }}

{% rvt_card raised=true %}
  {% rvt_card_image src="/hero.webp" alt="" %}
  {% rvt_card_body title="Campus life" title_href="/life" heading_level=2 %}
    <p>Each of our nine campuses…</p>
  {% endrvt_card_body %}
{% endrvt_card %}
```

```latte
{rvtButton 'Save changes', purpose: danger /}

{rvtCard raised: true}
  {rvtCardImage src: '/hero.webp', alt: ''}
  {rvtCardBody title: 'Campus life', titleHref: '/life', headingLevel: 2}
    <p>Each of our nine campuses…</p>
  {/rvtCardBody}
{/rvtCard}
```

Both produce byte-identical markup, and a test asserts that for every component. Whole
pages differ only in the whitespace between elements, because the two engines treat the
newline after a tag differently.

### Arguments

- **Write argument names in snake_case.** Twig cannot lex a hyphen inside tag syntax at
  all, so `helper_text` is the only spelling that works in both engines; it maps to the
  `helperText` parameter.
- **The first argument may be positional**: `rvt_badge('New', style: 'info')`.
- **Anything that is not a parameter becomes an HTML attribute**, with underscores mapped
  to hyphens: `data_testid: 'save'` emits `data-testid="save"`.
- **`class` merges** rather than replaces — your classes are appended after the
  component's.
- **Enums take their string value**: `style: 'danger'`, not a PHP enum case.

### From plain PHP

The components have no dependency on either engine, so they work anywhere:

```php
use Guild\Rivet\Component\Alert;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Render\RenderContext;

echo new Alert('Maintenance', style: AlertStyle::Warning)
    ->render(new RenderContext(), '<p>Back on Tuesday.</p>');
```

## Seeing it run

```bash
php -S localhost:8080 -t examples/public
```

Renders the highlight components with Rivet's real CSS and JavaScript loaded, through
both engines — `?engine=twig` and `?engine=latte` — with a banner comparing the two.

## What the library takes care of

**Identifiers.** Rivet needs the same string in up to six places on a dialog, five on a
file input, and a distinct one per form control. Its own documentation hard-codes them
(`dialog-title`, `file-description`, `id="search"`), so two examples copied onto one page
are already broken. Every identifier here is generated per render and derived
consistently.

**The ARIA split.** Which attributes you author and which Rivet's JavaScript generates
differs per component. A disclosure takes `aria-expanded`; an accordion, dropdown and tab
set must be given none at all or they conflict with Rivet's init. That is encoded per
component.

**Accessible names.** A button with no text, a switch with no label, a segmented group
with no name, an avatar image with no alt text — each is rejected rather than rendered
silently wrong.

**Form wiring.** `rvt_form_field` owns one id, binds the label to the control, and points
`aria-describedby` at exactly the helper and error sections that rendered. A field with
errors marks its control invalid without being asked.

## Components

49 components. `block` takes a body; `leaf` does not.

| Component | | Notes |
|---|---|---|
| `rvt_accordion` | block | container for `rvt_accordion_panel` |
| `rvt_accordion_panel` | block | requires `rvt_accordion` |
| `rvt_alert` | block | dismissible page-level message |
| `rvt_avatar` | leaf | image or initials, max 2 |
| `rvt_badge` | leaf | |
| `rvt_breadcrumbs` | leaf | `items` array; last item is the current page |
| `rvt_button` | block | `text` for the short form, or a body for icon + label |
| `rvt_button_group` | block | |
| `rvt_card` | block | with `rvt_card_image`, `rvt_card_body` |
| `rvt_card_body` | block | requires `rvt_card` |
| `rvt_card_image` | leaf | requires `rvt_card` |
| `rvt_checkbox` | leaf | valid alone or inside `rvt_field_group` |
| `rvt_column` | block | width 1–12, scoped to `-md` and up |
| `rvt_container` | block | |
| `rvt_dialog` | block | renders its own trigger; with `rvt_dialog_body`, `rvt_dialog_controls` |
| `rvt_dialog_body` | block | requires `rvt_dialog`; supplies the described-by target |
| `rvt_dialog_controls` | block | requires `rvt_dialog` |
| `rvt_disclosure` | block | |
| `rvt_dropdown` | block | menu items are bare links or buttons |
| `rvt_field_group` | block | fieldset + legend for checkboxes and radios |
| `rvt_file_input` | leaf | |
| `rvt_footer` | leaf | IU-required links and copyright built in |
| `rvt_form_field` | block | label, control, helper text, errors |
| `rvt_header` | leaf | `items` nav tree; children become a dropdown |
| `rvt_icon` | leaf | needs the `rivet-icons` package |
| `rvt_inline_alert` | block | validation message; one glyph per severity |
| `rvt_input_group` | block | with `rvt_input_group_addon` |
| `rvt_input_group_addon` | block | requires `rvt_input_group` |
| `rvt_list` | block | |
| `rvt_loader` | leaf | |
| `rvt_page` | block | assembles the whole document; needs `PageDefaults` |
| `rvt_page_breadcrumbs` | block | slot; lands in the heading band |
| `rvt_page_scripts` | block | slot; lands before `</body>`, after Rivet's script |
| `rvt_page_sidebar` | block | slot; requires the `sidebar` or `anchored_sidebar` layout |
| `rvt_page_styles` | block | slot; lands in `<head>`, after Rivet's stylesheet |
| `rvt_pagination` | leaf | unavailable arrows drop the link entirely |
| `rvt_radio` | leaf | **requires** `rvt_field_group` |
| `rvt_row` | block | |
| `rvt_segmented_buttons` | block | `label` required |
| `rvt_select` | leaf | `options` array; requires `rvt_form_field` |
| `rvt_sidenav` | leaf | recursive `items`, max 4 levels |
| `rvt_step_indicator` | leaf | |
| `rvt_subnav` | leaf | |
| `rvt_switch` | leaf | `label` required |
| `rvt_tab` | block | requires `rvt_tabs`; renders through its parent |
| `rvt_table` | block | caption always emitted |
| `rvt_tabs` | block | `label` required |
| `rvt_text_input` | leaf | requires `rvt_form_field` |
| `rvt_textarea` | leaf | requires `rvt_form_field` |

Run `composer components` for the full parameter list of each.

## Errors

| Exception | When |
|---|---|
| `Exception\ComponentContextException` | a component was used outside the parent it needs — `rvt_radio` outside `rvt_field_group`, `rvt_card_body` outside `rvt_card`. Both engines also catch this at compile time, with a file and line. |
| `Exception\InvalidArgumentException` | a value cannot be rendered: an unknown component name, a button with no accessible name, a column width outside 1–12, more than two avatar initials, a combination Rivet has no class for. |
| `Exception\RivetException` | the interface both implement, for catching anything from this package. |

## Rivet 3

Rivet 3 is in development and keeps the `rvt-` classes and every `data-rvt-*` attribute,
so the identifier wiring carries over unchanged. Button modifiers and a few block names
do change, and Header and Footer are rewritten. Treat a `beta` tag on
[`@rivet-iu/core`](https://registry.npmjs.org/@rivet-iu/core) as the signal to revisit.

## Contributing

See [AGENTS.md](AGENTS.md). It is written for AI coding agents but is the most complete
developer documentation for this project.
