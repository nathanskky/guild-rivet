# Page Layouts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a `Page` component that assembles a complete Rivet blank-page document, in all three published variants, so an application's page template carries only page-specific content.

**Architecture:** A block component with registering slot sub-components — the `Tabs`/`Tab` mechanism already in the library — rather than template inheritance, because Latte's `FileLoader` cannot load a layout shipped from a vendor package. Application-wide values live in an immutable `PageDefaults` reaching the page through `RenderContext`. A marker interface lets opening a page reset id numbering before its children render.

**Tech Stack:** PHP 8.5, PHPUnit 13, PHPStan max, Pint PSR-12, Twig 3.28, Latte 3.1.

**Spec:** `docs/specs/2026-09-14-page-layout-design.md`

## Global Constraints

- PHP `~8.5.0`. PHPStan runs at `level: max` over `src` and **must stay green** — this package has no known-red baseline.
- `composer check` (test, analyse, format:check) must pass before every commit. Run `composer format` rather than hand-fixing style.
- Never move `twig/twig` or `latte/latte` into `require`; they stay `suggest` + `require-dev`.
- Every `rvt-*` class literal lives in one place per component, normally a `BLOCK` constant.
- Markup is read from Rivet's published pages, never reconstructed. All markup in this plan was verified against `https://rivet.iu.edu/layouts/preview/blank-page/{variant}/`.
- Argument names reaching templates are snake_case; `ComponentFactory` maps them to camelCase parameters.
- Test style follows `access/tests/`: `final class`, `#[CoversClass]`, `self::assert*` with an intent message, `public static` data providers returning `iterable`, no mocks.
- Targeted Rivet release is `Rivet::VERSION` (`2.9.1`). Rivet icons are `3.0.1`.

---

### Task 1: Configuration value objects

**Files:**
- Create: `src/Page/RivetAssets.php`
- Create: `src/Page/PageDefaults.php`
- Test: `tests/Page/RivetAssetsTest.php`

**Interfaces:**
- Consumes: `Guild\Rivet\Rivet::VERSION`, `Guild\Rivet\Enum\ContainerSize`
- Produces:
  - `RivetAssets::__construct(bool $enabled = true, bool $icons = true, string $version = Rivet::VERSION, string $iconsVersion = '3.0.1', ?string $cssHref = null, ?string $jsSrc = null, ?string $iconsCssHref = null, ?string $iconsJsSrc = null)`
  - `RivetAssets::coreCss(): string`, `coreJs(): string`, `iconsCss(): string`, `iconsJs(): string`
  - `PageDefaults::__construct(string $appTitle, ?string $appSubtitle = null, string $homeHref = '/', array $navItems = [], ?string $searchAction = null, array $footerLinks = [], bool $footerLight = false, ContainerSize $containerSize = ContainerSize::Large, string $lang = 'en', string $titleSeparator = ' · ', ?string $description = null, RivetAssets $assets = new RivetAssets())` — all properties `public readonly`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Page;

use Guild\Rivet\Page\RivetAssets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RivetAssets::class)]
final class RivetAssetsTest extends TestCase
{
    public function testUrlsAreBuiltFromTheVersionByDefault(): void
    {
        $assets = new RivetAssets();

        self::assertSame(
            'https://unpkg.com/rivet-core@2.9.1/css/rivet.min.css',
            $assets->coreCss(),
            'The pinned version is the single place a Rivet release is named.',
        );
        self::assertSame('https://unpkg.com/rivet-core@2.9.1/js/rivet.min.js', $assets->coreJs());
        self::assertSame(
            'https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icon-element.css',
            $assets->iconsCss(),
        );
        self::assertSame('https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icons.js', $assets->iconsJs());
    }

    public function testAVersionChangeFlowsIntoEveryUrl(): void
    {
        self::assertStringContainsString(
            'rivet-core@2.8.1',
            new RivetAssets(version: '2.8.1')->coreCss(),
            'An application pinned to an older Rivet must be able to say so once.',
        );
    }

    public function testAnExplicitHrefOverridesTheCdnUrl(): void
    {
        self::assertSame(
            '/assets/rivet.css',
            new RivetAssets(cssHref: '/assets/rivet.css')->coreCss(),
            'Self-hosting must not require turning the feature off entirely.',
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Page/RivetAssetsTest.php`
Expected: FAIL — `Class "Guild\Rivet\Page\RivetAssets" not found`

- [ ] **Step 3: Write the implementation**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Page;

use Guild\Rivet\Rivet;

/**
 * Where a page finds Rivet's own stylesheet and script.
 *
 * Held apart from PageDefaults so that object stays readable, and so a self-hosted
 * application can point at its own files rather than disabling the tags altogether.
 * A version is named once here and flows into every URL.
 */
final readonly class RivetAssets
{
    private const string CDN = 'https://unpkg.com';

    public function __construct(
        public bool $enabled = true,
        public bool $icons = true,
        public string $version = Rivet::VERSION,
        public string $iconsVersion = '3.0.1',
        public ?string $cssHref = null,
        public ?string $jsSrc = null,
        public ?string $iconsCssHref = null,
        public ?string $iconsJsSrc = null,
    ) {}

    public function coreCss(): string
    {
        return $this->cssHref ?? self::CDN . '/rivet-core@' . $this->version . '/css/rivet.min.css';
    }

    public function coreJs(): string
    {
        return $this->jsSrc ?? self::CDN . '/rivet-core@' . $this->version . '/js/rivet.min.js';
    }

    public function iconsCss(): string
    {
        return $this->iconsCssHref
            ?? self::CDN . '/rivet-icons@' . $this->iconsVersion . '/dist/rivet-icon-element.css';
    }

    public function iconsJs(): string
    {
        return $this->iconsJsSrc
            ?? self::CDN . '/rivet-icons@' . $this->iconsVersion . '/dist/rivet-icons.js';
    }
}
```

Then `src/Page/PageDefaults.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Page;

use Guild\Rivet\Enum\ContainerSize;

/**
 * Everything a page needs that is identical on every page of an application.
 *
 * Configured once and reached through RenderContext, so a page template carries only
 * what is specific to that page. Only the fields applications actually vary appear here:
 * Footer already defaults IU's required accessibility, privacy and copyright links, so
 * those are not repeated.
 *
 * @phpstan-type NavItem array{label?: string, href?: string, current?: bool, children?: array<int, mixed>}
 */
final readonly class PageDefaults
{
    /**
     * @param  list<mixed>  $navItems  header navigation tree, as Header accepts
     * @param  list<array{label?: string, href?: string}>  $footerLinks  extra footer links
     */
    public function __construct(
        public string $appTitle,
        public ?string $appSubtitle = null,
        public string $homeHref = '/',
        public array $navItems = [],
        public ?string $searchAction = null,
        public array $footerLinks = [],
        public bool $footerLight = false,
        public ContainerSize $containerSize = ContainerSize::Large,
        public string $lang = 'en',
        public string $titleSeparator = ' · ',
        public ?string $description = null,
        public RivetAssets $assets = new RivetAssets(),
    ) {}

    /**
     * The document title: the page's own, then the application's.
     */
    public function documentTitle(?string $pageTitle): string
    {
        if ($pageTitle === null || $pageTitle === '') {
            return $this->appTitle;
        }

        return $pageTitle . $this->titleSeparator . $this->appTitle;
    }
}
```

- [ ] **Step 4: Run tests and the full check**

Run: `vendor/bin/phpunit tests/Page/RivetAssetsTest.php` — Expected: PASS
Run: `composer check` — Expected: all green

- [ ] **Step 5: Commit**

```bash
git add src/Page tests/Page
git commit -m "Add page configuration value objects

RivetAssets names a Rivet release once and builds every URL from it, with
per-file overrides so a self-hosted application need not disable the tags
to point at its own copies.

PageDefaults holds what is identical on every page. Only fields applications
actually vary are exposed; Footer already defaults IU's required links."
```

---

### Task 2: Render context carries page defaults, and a page resets it

**Files:**
- Create: `src/Render/StartsRender.php`
- Create: `src/Enum/PageLayout.php`
- Modify: `src/Render/RenderContext.php`
- Modify: `src/Render/Renderer.php`
- Test: `tests/Render/StartsRenderTest.php`

**Interfaces:**
- Consumes: `PageDefaults` from Task 1
- Produces:
  - `interface StartsRender {}` (marker, no methods)
  - `RenderContext::__construct(?IdGenerator $ids = null, ?PageDefaults $pageDefaults = null)`
  - `RenderContext::pageDefaults(): ?PageDefaults`
  - `Renderer::__construct(ComponentRegistry $registry, ComponentFactory $factory = new ComponentFactory(), ?PageDefaults $pageDefaults = null)`
  - `Renderer::reset(?IdGenerator $ids = null): void` — preserves the defaults it was constructed with
  - `enum PageLayout: string { SingleColumn = 'single_column'; Sidebar = 'sidebar'; AnchoredSidebar = 'anchored_sidebar'; }`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Test\Render\Fixture\DocumentStub;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Renderer::class)]
#[CoversClass(RenderContext::class)]
final class StartsRenderTest extends TestCase
{
    public function testTheContextCarriesTheConfiguredDefaults(): void
    {
        $defaults = new PageDefaults(appTitle: 'Course Catalog');

        self::assertSame(
            $defaults,
            new RenderContext(pageDefaults: $defaults)->pageDefaults(),
            'A page reads application-wide configuration from the context, as it does the id generator.',
        );
    }

    public function testAContextWithNoDefaultsSaysSo(): void
    {
        self::assertNull(new RenderContext()->pageDefaults());
    }

    public function testOpeningAPageResetsIdNumbering(): void
    {
        $renderer = $this->renderer();
        $renderer->render($renderer->open('rvt_alert', ['title' => 'Before']), 'x');

        $frame = $renderer->open('rvt_document', []);

        self::assertSame(
            'rvt-alert-1',
            $renderer->context()->ids()->next('rvt-alert'),
            'A page is the start of a render, so numbering begins again and the same page always emits the same ids.',
        );
        $renderer->close($frame);
    }

    public function testTheResetPreservesTheConfiguredDefaults(): void
    {
        $defaults = new PageDefaults(appTitle: 'Course Catalog');
        $renderer = $this->renderer($defaults);

        $frame = $renderer->open('rvt_document', []);

        self::assertSame(
            $defaults,
            $renderer->context()->pageDefaults(),
            'Resetting starts a fresh render, not a fresh application.',
        );
        $renderer->close($frame);
    }

    public function testAPageInsideAnotherComponentIsRejected(): void
    {
        $renderer = $this->renderer();
        $renderer->open('rvt_alert', ['title' => 'Outer']);

        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_document cannot be nested inside another component.');

        $renderer->open('rvt_document', []);
    }

    private function renderer(?PageDefaults $defaults = null): Renderer
    {
        return new Renderer(
            new ComponentRegistry([Alert::class, Badge::class, DocumentStub::class]),
            pageDefaults: $defaults,
        );
    }
}
```

And the fixture, `tests/Render/Fixture/DocumentStub.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render\Fixture;

use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\StartsRender;

/**
 * A component that begins a render, without the Page component's markup concerns.
 */
final class DocumentStub extends StubComponent implements StartsRender
{
    public static function name(): string
    {
        return 'rvt_document';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return $content;
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Render/StartsRenderTest.php`
Expected: FAIL — `Class "Guild\Rivet\Render\StartsRender" not found`

- [ ] **Step 3: Write the implementation**

`src/Render/StartsRender.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

/**
 * Marks a component that begins a render — in practice, a whole document.
 *
 * Opening one resets the render context, which is the only point early enough to matter:
 * a component's children render before it does, so a reset performed during its own
 * render would come too late for anything nested inside it.
 *
 * Neither Twig nor Latte offers a reliable "top-level render started" hook, so the
 * outermost component is the signal.
 */
interface StartsRender {}
```

`src/Enum/PageLayout.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * Which blank-page layout a page uses.
 *
 * These are structurally different documents rather than one structure with modifiers.
 * Under AnchoredSidebar `<main>` becomes the layout wrapper itself, which leaves nowhere
 * above it for the full-bleed heading band, so the heading moves inside the content.
 *
 * @see https://rivet.iu.edu/layouts/blank-page/
 */
enum PageLayout: string
{
    case SingleColumn = 'single_column';
    case Sidebar = 'sidebar';
    case AnchoredSidebar = 'anchored_sidebar';
}
```

In `src/Render/RenderContext.php`, add the constructor parameter and accessor:

```php
    private readonly IdGenerator $ids;

    public function __construct(
        ?IdGenerator $ids = null,
        private readonly ?PageDefaults $pageDefaults = null,
    ) {
        $this->ids = $ids ?? new SequentialIdGenerator();
    }

    /**
     * Application-wide page configuration, or null when none was supplied.
     */
    public function pageDefaults(): ?PageDefaults
    {
        return $this->pageDefaults;
    }
```

Add `use Guild\Rivet\Page\PageDefaults;` to its imports.

In `src/Render/Renderer.php`, accept the defaults, preserve them across `reset()`, and reset when opening a `StartsRender` component:

```php
    private RenderContext $context;

    public function __construct(
        private readonly ComponentRegistry $registry,
        private readonly ComponentFactory $factory = new ComponentFactory(),
        private readonly ?PageDefaults $pageDefaults = null,
    ) {
        $this->context = new RenderContext(pageDefaults: $this->pageDefaults);
    }

    public function reset(?IdGenerator $ids = null): void
    {
        $this->context = new RenderContext($ids, $this->pageDefaults);
    }

    public function open(string $name, array $arguments): RenderFrame
    {
        $component = $this->component($name, $arguments);

        if ($component instanceof StartsRender) {
            // A document cannot sit inside anything, and the reset below would silently
            // renumber whatever was already open.
            if ($this->context->isEmpty() === false) {
                throw new ComponentContextException(sprintf(
                    '%s cannot be nested inside another component.',
                    $component::name(),
                ));
            }

            $this->reset();
        }

        $this->context->open($component);

        return new RenderFrame($component);
    }
```

Add `use Guild\Rivet\Exception\ComponentContextException;` and `use Guild\Rivet\Page\PageDefaults;` to `Renderer`.

Add to `RenderContext`:

```php
    /**
     * Whether nothing is currently open.
     */
    public function isEmpty(): bool
    {
        return $this->stack === [];
    }
```

- [ ] **Step 4: Run tests and the full check**

Run: `vendor/bin/phpunit tests/Render/StartsRenderTest.php` — Expected: PASS
Run: `composer check` — Expected: all green (the existing suite must still pass; `Renderer`'s new parameter is optional and defaults preserve current behaviour)

- [ ] **Step 5: Commit**

```bash
git add src/Render/StartsRender.php src/Enum/PageLayout.php src/Render/RenderContext.php src/Render/Renderer.php tests/Render
git commit -m "Let a component begin a render, and carry page defaults on the context

StartsRender marks a component that is a whole document. Opening one resets
the render context, which is the only point early enough: a component's
children render before it does, so resetting during its own render would
come too late for anything nested inside.

The reset makes any page render reproducible - the same page always emits
the same identifiers - and removes the reset call an integration would
otherwise have to remember.

Opening such a component while anything else is open is rejected, since a
document cannot sit inside another component and the reset would silently
renumber whatever was already open.

PageDefaults joins the id generator on RenderContext: both are render-scoped,
and the defaults survive a reset because that starts a fresh render, not a
fresh application."
```

---

### Task 3: The page, single-column layout

**Files:**
- Create: `src/Component/Page/Page.php`
- Test: `tests/Component/Page/PageTest.php`

**Interfaces:**
- Consumes: `PageDefaults`, `RivetAssets` (Task 1); `StartsRender`, `PageLayout` (Task 2); existing `Header`, `Footer`, `Html`, `Attributes`
- Produces:
  - `Page::__construct(?string $title = null, ?string $heading = null, ?string $description = null, PageLayout $layout = PageLayout::SingleColumn, Attributes $extra = new Attributes())`
  - `Page::name(): string` → `'rvt_page'`; `Page::acceptsContent(): bool` → `true`
  - `Page::addStyles(string $html): void`, `addScripts(string $html): void`, `setSidebar(string $html): void`, `setBreadcrumbs(string $html): void` — called by the slots in Task 4
  - `Page::layout(): PageLayout`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Exception\ConfigurationException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Page\RivetAssets;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Page::class)]
final class PageTest extends TestCase
{
    public function testTheDocumentOpensCorrectly(): void
    {
        self::assertStringStartsWith(
            '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Chemistry · Course Catalog</title>',
            $this->render(new Page(title: 'Chemistry')),
            'The page owns the whole document, so nothing above body is left to the application.',
        );
    }

    public function testThePageTitleIsOptional(): void
    {
        self::assertStringContainsString(
            '<title>Course Catalog</title>',
            $this->render(new Page()),
            'A page with no title of its own is named by the application.',
        );
    }

    public function testRivetAssetsAreEmittedByDefault(): void
    {
        $html = $this->render(new Page());

        self::assertStringContainsString('href="https://unpkg.com/rivet-core@2.9.1/css/rivet.min.css"', $html);
        self::assertStringContainsString('src="https://unpkg.com/rivet-core@2.9.1/js/rivet.min.js"', $html);
        self::assertStringContainsString('<script>Rivet.init()</script>', $html, 'Rivet does nothing until init is called.');
        self::assertStringContainsString('rivet-icons@3.0.1', $html);
    }

    public function testAssetsCanBeTurnedOff(): void
    {
        $html = $this->render(new Page(), new PageDefaults(
            appTitle: 'Course Catalog',
            assets: new RivetAssets(enabled: false, icons: false),
        ));

        self::assertStringNotContainsString('unpkg.com', $html, 'An application serving Rivet itself must be able to opt out.');
        self::assertStringNotContainsString('Rivet.init()', $html);
    }

    public function testTheBodyCarriesTheLayoutClassAndTheContentIsInsideMain(): void
    {
        $html = $this->render(new Page(), content: '<p>Body</p>');

        self::assertStringContainsString('<body class="rvt-layout">', $html);
        self::assertStringContainsString(
            '<main id="main-content" class="rvt-flex rvt-flex-column rvt-grow-1">',
            $html,
            'Rivet puts main outside the layout wrapper, which is what makes the header skip link land correctly.',
        );
        self::assertStringContainsString(
            '<div class="rvt-layout__wrapper rvt-p-tb-xxl"><div class="rvt-container-lg"><p>Body</p></div></div>',
            $html,
        );
    }

    public function testTheHeadingBandAppearsOnlyWhenThereIsAHeading(): void
    {
        self::assertStringNotContainsString(
            'rvt-border-bottom',
            $this->render(new Page()),
            'A page with neither heading nor breadcrumbs has nothing to put in the band.',
        );

        self::assertStringContainsString(
            '<div class="rvt-bg-black-000 rvt-border-bottom rvt-p-top-xl">'
            . '<div class="rvt-container-lg rvt-prose rvt-flow rvt-p-bottom-xl">'
            . '<h1 class="rvt-m-top-xs">Chemistry</h1>'
            . '</div></div>',
            $this->render(new Page(heading: 'Chemistry')),
        );
    }

    public function testTheHeaderAndFooterComeFromTheDefaults(): void
    {
        $html = $this->render(new Page());

        self::assertStringContainsString('<span class="rvt-lockup__title">Course Catalog</span>', $html);
        self::assertStringContainsString('<span class="rvt-lockup__subtitle">Indiana University</span>', $html);
        self::assertStringContainsString('rvt-footer-base', $html);
    }

    public function testADescriptionIsEmittedAndThePageOverridesTheApplication(): void
    {
        self::assertStringContainsString(
            '<meta name="description" content="All chemistry courses">',
            $this->render(new Page(description: 'All chemistry courses')),
        );
    }

    public function testAPageWithoutConfiguredDefaultsSaysHowToFixIt(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            'rvt_page needs PageDefaults. Pass one to the Renderer constructor, or to addRivet() in a Guild application.'
        );

        new Page()->render(new RenderContext());
    }

    private function render(Page $page, ?PageDefaults $defaults = null, string $content = ''): string
    {
        return $page->render(
            new RenderContext(pageDefaults: $defaults ?? new PageDefaults(
                appTitle: 'Course Catalog',
                appSubtitle: 'Indiana University',
            )),
            $content,
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Component/Page/PageTest.php`
Expected: FAIL — `Class "Guild\Rivet\Component\Page\Page" not found`

- [ ] **Step 3: Add the exception, then the component**

`src/Exception/ConfigurationException.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Exception;

use LogicException;

/**
 * The library was used in a way that needs configuration it was not given.
 *
 * Distinct from InvalidArgumentException, which is about a value that cannot be
 * rendered. This is about something the application was supposed to set up once.
 */
final class ConfigurationException extends LogicException implements RivetException {}
```

`src/Component/Page/Page.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Component\Footer;
use Guild\Rivet\Component\Header;
use Guild\Rivet\Enum\PageLayout;
use Guild\Rivet\Exception\ConfigurationException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\StartsRender;

/**
 * A complete Rivet blank-page document.
 *
 * Assembles doctype, head, header, layout, footer and scripts, so an application's page
 * template carries only what is specific to that page. Application-wide values come from
 * PageDefaults; per-page regions come from the slot components, which register with this
 * one as the body is captured.
 *
 * Implements StartsRender, so opening a page restarts id numbering and the same page
 * always renders identically.
 *
 * @see https://rivet.iu.edu/layouts/blank-page/
 */
final class Page extends Component implements StartsRender
{
    private string $styles = '';

    private string $scripts = '';

    private string $sidebar = '';

    private string $breadcrumbs = '';

    public function __construct(
        private readonly ?string $title = null,
        private readonly ?string $heading = null,
        private readonly ?string $description = null,
        private readonly PageLayout $layout = PageLayout::SingleColumn,
        private readonly Attributes $extra = new Attributes(),
    ) {}

    public static function name(): string
    {
        return 'rvt_page';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function layout(): PageLayout
    {
        return $this->layout;
    }

    public function addStyles(string $html): void
    {
        $this->styles .= $html;
    }

    public function addScripts(string $html): void
    {
        $this->scripts .= $html;
    }

    public function setSidebar(string $html): void
    {
        $this->sidebar = $html;
    }

    public function setBreadcrumbs(string $html): void
    {
        $this->breadcrumbs = $html;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $defaults = $context->pageDefaults() ?? throw new ConfigurationException(
            'rvt_page needs PageDefaults. Pass one to the Renderer constructor, '
            . 'or to addRivet() in a Guild application.'
        );

        return '<!doctype html>'
            . Html::el('html')
                ->attr('lang', $defaults->lang)
                ->merge($this->extra)
                ->html($this->head($defaults) . $this->body($context, $defaults, $content))
                ->render();
    }

    private function head(PageDefaults $defaults): string
    {
        $head = Html::el('head')
            ->html('<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">')
            ->children(Html::el('title')->text($defaults->documentTitle($this->title)));

        $description = $this->description ?? $defaults->description;

        if ($description !== null && $description !== '') {
            $head->children(Html::el('meta')->attr('name', 'description')->attr('content', $description));
        }

        if ($defaults->assets->enabled) {
            $head->children(Html::el('link')->attr('rel', 'stylesheet')->attr('href', $defaults->assets->coreCss()));
        }

        if ($defaults->assets->icons) {
            $head->children(Html::el('link')->attr('rel', 'stylesheet')->attr('href', $defaults->assets->iconsCss()));
        }

        return $head->html($this->styles)->render();
    }

    private function body(RenderContext $context, PageDefaults $defaults, string $content): string
    {
        return Html::el('body')
            ->class('rvt-layout')
            ->html(
                $this->header($context, $defaults)
                . $this->main($defaults, $content)
                . $this->footer($context, $defaults)
                . $this->trailingScripts($defaults)
            )
            ->render();
    }

    private function header(RenderContext $context, PageDefaults $defaults): string
    {
        return new Header(
            title: $defaults->appTitle,
            subtitle: $defaults->appSubtitle,
            href: $defaults->homeHref,
            items: $defaults->navItems,
            containerSize: $defaults->containerSize,
            searchAction: $defaults->searchAction,
        )->render($context);
    }

    private function footer(RenderContext $context, PageDefaults $defaults): string
    {
        return new Footer(
            links: $defaults->footerLinks,
            light: $defaults->footerLight,
            containerSize: $defaults->containerSize,
        )->render($context);
    }

    private function main(PageDefaults $defaults, string $content): string
    {
        return Html::el('main')
            ->attr('id', 'main-content')
            ->class('rvt-flex', 'rvt-flex-column', 'rvt-grow-1')
            ->html($this->headingBand($defaults) . $this->wrapper($defaults, $content))
            ->render();
    }

    /**
     * The shaded, full-bleed band holding breadcrumbs and the page heading.
     *
     * Omitted entirely when there is neither.
     */
    private function headingBand(PageDefaults $defaults): string
    {
        if ($this->heading === null && $this->breadcrumbs === '') {
            return '';
        }

        $inner = Html::el('div')
            ->class($defaults->containerSize->value, 'rvt-prose', 'rvt-flow', 'rvt-p-bottom-xl')
            ->html($this->breadcrumbs);

        if ($this->heading !== null) {
            $inner->children(Html::el('h1')->class('rvt-m-top-xs')->text($this->heading));
        }

        return Html::el('div')
            ->class('rvt-bg-black-000', 'rvt-border-bottom', 'rvt-p-top-xl')
            ->children($inner)
            ->render();
    }

    private function wrapper(PageDefaults $defaults, string $content): string
    {
        return Html::el('div')
            ->class('rvt-layout__wrapper', 'rvt-p-tb-xxl')
            ->children(Html::el('div')->class($defaults->containerSize->value)->html($content))
            ->render();
    }

    private function trailingScripts(PageDefaults $defaults): string
    {
        $scripts = '';

        if ($defaults->assets->enabled) {
            $scripts .= Html::el('script')->attr('src', $defaults->assets->coreJs())->render()
                . '<script>Rivet.init()</script>';
        }

        if ($defaults->assets->icons) {
            $scripts .= Html::el('script')
                ->attr('type', 'module')
                ->attr('src', $defaults->assets->iconsJs())
                ->render();
        }

        return $scripts . $this->scripts;
    }
}
```

Note: `Html::el('link')` and `Html::el('meta')` are void elements, already handled by `Html`.

- [ ] **Step 4: Run tests and the full check**

Run: `vendor/bin/phpunit tests/Component/Page/PageTest.php` — Expected: PASS
Run: `composer check` — Expected: all green

- [ ] **Step 5: Commit**

```bash
git add src/Component/Page src/Exception/ConfigurationException.php tests/Component/Page
git commit -m "Add the page component, single-column layout

Assembles a whole Rivet blank-page document so an application's template
carries only page-specific content. Application-wide values come from
PageDefaults; the heading band is omitted when there is nothing to put in it.

Rivet's own blank-page markup puts main outside the layout wrapper, which is
what makes the header skip link land past the navigation rather than before
it; that structure is reproduced exactly.

The page emits Rivet's own asset tags, which reverses the earlier decision
that asset delivery was out of scope. RivetAssets switches them off for an
application that serves Rivet itself."
```

---

### Task 4: The slot components

**Files:**
- Create: `src/Component/Page/PageSlot.php`
- Create: `src/Component/Page/PageStyles.php`, `PageScripts.php`, `PageSidebar.php`, `PageBreadcrumbs.php`
- Test: `tests/Component/Page/PageSlotTest.php`

**Interfaces:**
- Consumes: `Page::addStyles()`, `addScripts()`, `setSidebar()`, `setBreadcrumbs()` from Task 3
- Produces: four components named `rvt_page_styles`, `rvt_page_scripts`, `rvt_page_sidebar`, `rvt_page_breadcrumbs`, each `acceptsContent(): true`, each rendering `''`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Component\Page\PageBreadcrumbs;
use Guild\Rivet\Component\Page\PageScripts;
use Guild\Rivet\Component\Page\PageStyles;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageStyles::class)]
#[CoversClass(PageScripts::class)]
#[CoversClass(PageBreadcrumbs::class)]
final class PageSlotTest extends TestCase
{
    public function testASlotRendersNothingWhereItIsWritten(): void
    {
        $context = $this->context();
        $context->open(new Page());

        self::assertSame(
            '',
            new PageStyles()->render($context, '<link rel="stylesheet" href="/a.css">'),
            'A slot registers its content with the page rather than emitting it in place.',
        );
    }

    public function testStylesLandInTheHeadAndScriptsAtTheEnd(): void
    {
        $context = $this->context();
        $page = new Page();
        $context->open($page);

        new PageStyles()->render($context, '<link rel="stylesheet" href="/a.css">');
        new PageScripts()->render($context, '<script src="/a.js"></script>');

        $html = $page->render($context, '<p>Body</p>');

        self::assertStringContainsString('<link rel="stylesheet" href="/a.css"></head>', $html);
        self::assertStringContainsString('<script src="/a.js"></script></body>', $html);
    }

    public function testBreadcrumbsLandInTheHeadingBandAboveTheHeading(): void
    {
        $context = $this->context();
        $page = new Page(heading: 'Chemistry');
        $context->open($page);

        new PageBreadcrumbs()->render($context, '<nav>crumbs</nav>');

        self::assertStringContainsString(
            '<nav>crumbs</nav><h1 class="rvt-m-top-xs">Chemistry</h1>',
            $page->render($context, 'x'),
        );
    }

    public function testBreadcrumbsAloneStillProduceTheBand(): void
    {
        $context = $this->context();
        $page = new Page();
        $context->open($page);

        new PageBreadcrumbs()->render($context, '<nav>crumbs</nav>');

        self::assertStringContainsString('rvt-border-bottom', $page->render($context, 'x'));
    }

    public function testASlotOutsideAPageIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_page_styles must be used inside rvt_page.');

        new PageStyles()->render($this->context(), 'x');
    }

    private function context(): RenderContext
    {
        return new RenderContext(pageDefaults: new PageDefaults(appTitle: 'Course Catalog'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Component/Page/PageSlotTest.php`
Expected: FAIL — `Class "Guild\Rivet\Component\Page\PageStyles" not found`

- [ ] **Step 3: Write the implementation**

`src/Component/Page/PageSlot.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Render\RenderContext;

/**
 * A region of the page written in the body but emitted somewhere else.
 *
 * Each slot hands its content to the enclosing page as the body is captured and renders
 * nothing where it was written, so a stylesheet can be declared next to the markup that
 * needs it and still reach the head. The same mechanism Tabs uses to emit every button
 * before every panel.
 */
abstract class PageSlot extends Component
{
    public static function acceptsContent(): bool
    {
        return true;
    }

    abstract protected function give(Page $page, string $content): void;

    public function render(RenderContext $context, string $content = ''): string
    {
        $this->give($context->requireAncestor(Page::class, static::class), $content);

        return '';
    }
}
```

`src/Component/Page/PageStyles.php`:

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

/**
 * Stylesheets and inline styles for one page, emitted at the end of the head.
 */
final class PageStyles extends PageSlot
{
    public static function name(): string
    {
        return 'rvt_page_styles';
    }

    protected function give(Page $page, string $content): void
    {
        $page->addStyles($content);
    }
}
```

`src/Component/Page/PageScripts.php` — identical shape, `name()` returns `'rvt_page_scripts'`, calls `$page->addScripts($content)`, docblock "Scripts for one page, emitted just before the closing body tag, after Rivet's own."

`src/Component/Page/PageSidebar.php` — `name()` returns `'rvt_page_sidebar'`, calls `$page->setSidebar($content)`, docblock "The sidebar region. Its content is whatever the page needs there, most often an rvt_sidenav."

`src/Component/Page/PageBreadcrumbs.php` — `name()` returns `'rvt_page_breadcrumbs'`, calls `$page->setBreadcrumbs($content)`, docblock "Breadcrumbs for one page, emitted in the heading band above the page heading — or inside the content region under the anchored sidebar layout, which has no band."

- [ ] **Step 4: Run tests and the full check**

Run: `vendor/bin/phpunit tests/Component/Page` — Expected: PASS
Run: `composer check` — Expected: all green

- [ ] **Step 5: Commit**

```bash
git add src/Component/Page tests/Component/Page
git commit -m "Add the page slot components

Each slot hands its content to the enclosing page as the body is captured
and renders nothing where it was written, so a stylesheet can be declared
next to the markup that needs it and still reach the head. The same
mechanism Tabs uses to emit every button before every panel.

A slot written outside a page is rejected by name, at compile time in both
engines."
```

---

### Task 5: The two sidebar layouts

**Files:**
- Modify: `src/Component/Page/Page.php` — branch `main()` on `layout`
- Test: `tests/Component/Page/PageLayoutTest.php`

**Interfaces:**
- Consumes: `PageLayout` (Task 2), `Page::setSidebar()` (Task 3)
- Produces: no new public API; `Page::render()` now honours all three `PageLayout` cases

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Component\Page\PageBreadcrumbs;
use Guild\Rivet\Component\Page\PageSidebar;
use Guild\Rivet\Enum\PageLayout;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Page::class)]
final class PageLayoutTest extends TestCase
{
    public function testTheSidebarLayoutPutsTheWrapperInsideMainWithAContainer(): void
    {
        $html = $this->render(PageLayout::Sidebar, '<nav>side</nav>', '<p>Body</p>');

        self::assertStringContainsString(
            '<div class="rvt-layout__wrapper rvt-layout__wrapper--details rvt-container-lg">'
            . '<div class="rvt-layout__sidebar rvt-p-top-xxl rvt-flow rvt-prose" id="section-nav"><nav>side</nav></div>'
            . '<div class="rvt-layout__content rvt-p-top-xxl"><p>Body</p></div>'
            . '</div>',
            $html,
        );
        self::assertStringContainsString(
            '<main id="main-content" class="rvt-flex rvt-flex-column rvt-grow-1">',
            $html,
            'With a contained sidebar, main is still the flex column above the wrapper.',
        );
    }

    public function testTheAnchoredLayoutMakesMainTheWrapper(): void
    {
        $html = $this->render(PageLayout::AnchoredSidebar, '<nav>side</nav>', '<p>Body</p>');

        self::assertStringContainsString(
            '<main id="main-content" class="rvt-layout__wrapper rvt-layout__wrapper--details">',
            $html,
            'Anchoring the sidebar to the viewport edge means main becomes the wrapper itself.',
        );
        self::assertStringContainsString(
            '<div class="rvt-layout__sidebar rvt-p-top-xxl rvt-p-left-md rvt-bg-black-000" id="section-nav">',
            $html,
        );
    }

    public function testTheAnchoredLayoutHasNoHeadingBandAndPutsTheHeadingInTheContent(): void
    {
        $context = $this->context();
        $page = new Page(heading: 'Chemistry', layout: PageLayout::AnchoredSidebar);
        $context->open($page);
        new PageSidebar()->render($context, '<nav>side</nav>');
        new PageBreadcrumbs()->render($context, '<nav>crumbs</nav>');

        $html = $page->render($context, '<p>Body</p>');

        self::assertStringNotContainsString(
            'rvt-border-bottom',
            $html,
            'Main is the wrapper here, so there is nowhere above it for a full-bleed band.',
        );
        self::assertStringContainsString(
            '<div class="rvt-prose"><nav>crumbs</nav><h1 class="rvt-m-top-xs">Chemistry</h1></div>',
            $html,
        );
    }

    public function testASidebarUnderTheSingleColumnLayoutIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'rvt_page_sidebar has nowhere to go in the single_column layout. Use the sidebar or anchored_sidebar layout.'
        );

        $this->render(PageLayout::SingleColumn, '<nav>side</nav>', 'x');
    }

    private function render(PageLayout $layout, string $sidebar, string $content): string
    {
        $context = $this->context();
        $page = new Page(layout: $layout);
        $context->open($page);
        new PageSidebar()->render($context, $sidebar);

        return $page->render($context, $content);
    }

    private function context(): RenderContext
    {
        return new RenderContext(pageDefaults: new PageDefaults(appTitle: 'Course Catalog'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Component/Page/PageLayoutTest.php`
Expected: FAIL — the sidebar layout assertions fail because `main()` always renders the single-column structure

- [ ] **Step 3: Rewrite `main()` and its helpers in `Page`**

Replace `main()`, `wrapper()` and `headingBand()` with:

```php
    private function main(PageDefaults $defaults, string $content): string
    {
        if ($this->sidebar !== '' && $this->layout === PageLayout::SingleColumn) {
            throw new InvalidArgumentException(sprintf(
                '%s has nowhere to go in the %s layout. Use the %s or %s layout.',
                PageSidebar::name(),
                PageLayout::SingleColumn->value,
                PageLayout::Sidebar->value,
                PageLayout::AnchoredSidebar->value,
            ));
        }

        // Anchoring the sidebar to the viewport edge means main becomes the layout
        // wrapper itself, which leaves nowhere above it for a full-bleed heading band.
        if ($this->layout === PageLayout::AnchoredSidebar) {
            return Html::el('main')
                ->attr('id', 'main-content')
                ->class('rvt-layout__wrapper', 'rvt-layout__wrapper--details')
                ->html($this->anchoredSidebar() . $this->anchoredContent($defaults, $content))
                ->render();
        }

        return Html::el('main')
            ->attr('id', 'main-content')
            ->class('rvt-flex', 'rvt-flex-column', 'rvt-grow-1')
            ->html($this->headingBand($defaults) . $this->wrapper($defaults, $content))
            ->render();
    }

    private function wrapper(PageDefaults $defaults, string $content): string
    {
        if ($this->layout === PageLayout::Sidebar) {
            return Html::el('div')
                ->class('rvt-layout__wrapper', 'rvt-layout__wrapper--details', $defaults->containerSize->value)
                ->children(
                    Html::el('div')
                        ->class('rvt-layout__sidebar', 'rvt-p-top-xxl', 'rvt-flow', 'rvt-prose')
                        ->attr('id', 'section-nav')
                        ->html($this->sidebar),
                    Html::el('div')->class('rvt-layout__content', 'rvt-p-top-xxl')->html($content),
                )
                ->render();
        }

        return Html::el('div')
            ->class('rvt-layout__wrapper', 'rvt-p-tb-xxl')
            ->children(Html::el('div')->class($defaults->containerSize->value)->html($content))
            ->render();
    }

    private function anchoredSidebar(): string
    {
        return Html::el('div')
            ->class('rvt-layout__sidebar', 'rvt-p-top-xxl', 'rvt-p-left-md', 'rvt-bg-black-000')
            ->attr('id', 'section-nav')
            ->html($this->sidebar)
            ->render();
    }

    private function anchoredContent(PageDefaults $defaults, string $content): string
    {
        $inner = Html::el('div')
            ->class(
                $defaults->containerSize->value,
                'rvt-m-top-xl',
                'rvt-m-left-none',
                'rvt-m-right-none',
                'rvt-p-right-none',
                'rvt-p-left-none',
            );

        $heading = $this->headingBlock();

        if ($heading !== '') {
            $inner->html($heading);
        }

        return Html::el('div')
            ->class('rvt-layout__content', 'rvt-p-top-xxl', 'rvt-p-lr-md', 'rvt-p-lr-xxl-md-up')
            ->children($inner->html($content))
            ->render();
    }

    /**
     * Breadcrumbs and the heading, without the band around them.
     */
    private function headingBlock(): string
    {
        if ($this->heading === null && $this->breadcrumbs === '') {
            return '';
        }

        $block = Html::el('div')->class('rvt-prose')->html($this->breadcrumbs);

        if ($this->heading !== null) {
            $block->children(Html::el('h1')->class('rvt-m-top-xs')->text($this->heading));
        }

        return $block->render();
    }

    private function headingBand(PageDefaults $defaults): string
    {
        if ($this->heading === null && $this->breadcrumbs === '') {
            return '';
        }

        $inner = Html::el('div')
            ->class($defaults->containerSize->value, 'rvt-prose', 'rvt-flow', 'rvt-p-bottom-xl')
            ->html($this->breadcrumbs);

        if ($this->heading !== null) {
            $inner->children(Html::el('h1')->class('rvt-m-top-xs')->text($this->heading));
        }

        return Html::el('div')
            ->class('rvt-bg-black-000', 'rvt-border-bottom', 'rvt-p-top-xl')
            ->children($inner)
            ->render();
    }
```

Add `use Guild\Rivet\Exception\InvalidArgumentException;` to `Page`.

- [ ] **Step 4: Run tests and the full check**

Run: `vendor/bin/phpunit tests/Component/Page` — Expected: PASS (all three layout tests plus Tasks 3 and 4)
Run: `composer check` — Expected: all green

- [ ] **Step 5: Commit**

```bash
git add src/Component/Page/Page.php tests/Component/Page/PageLayoutTest.php
git commit -m "Add the two sidebar page layouts

Rivet's three blank-page variants are structurally different documents
rather than one structure with modifiers. With a contained sidebar, main
stays the flex column and the wrapper sits inside it carrying the container.
With an anchored sidebar, main becomes the wrapper itself so the sidebar can
run flush to the viewport edge - which leaves nowhere above it for the
full-bleed heading band, so breadcrumbs and the heading move inside the
content region instead.

A sidebar supplied under the single-column layout is rejected rather than
silently dropped, since the content would otherwise vanish with no error."
```

---

### Task 6: Registry, engine coverage and cross-engine parity

**Files:**
- Modify: `src/Rivet.php`
- Modify: `tests/CrossEngineParityTest.php`
- Test: `tests/Component/Page/PageEngineTest.php`

**Interfaces:**
- Consumes: every component from Tasks 3–5
- Produces: `rvt_page` and its four slots available as tags in both engines

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Latte\RivetExtension as LatteExtension;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Rivet;
use Guild\Rivet\Twig\RivetExtension as TwigExtension;
use Guild\Rivet\Twig\RivetRuntime;
use Latte\Engine as LatteEngine;
use Latte\Loaders\StringLoader;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

final class PageEngineTest extends TestCase
{
    private const string TWIG = <<<'TWIG'
        {% rvt_page title="Chemistry" heading="Chemistry" layout="sidebar" %}
        {% rvt_page_sidebar %}<nav>side</nav>{% endrvt_page_sidebar %}
        {% rvt_page_styles %}<link rel="stylesheet" href="/a.css">{% endrvt_page_styles %}
        <p>Body</p>
        {% endrvt_page %}
        TWIG;

    private const string LATTE = <<<'LATTE'
        {rvtPage title: 'Chemistry', heading: 'Chemistry', layout: 'sidebar'}
        {rvtPageSidebar}<nav>side</nav>{/rvtPageSidebar}
        {rvtPageStyles}<link rel="stylesheet" href="/a.css">{/rvtPageStyles}
        <p>Body</p>
        {/rvtPage}
        LATTE;

    public function testAPageRendersThroughTwig(): void
    {
        $html = $this->twig(self::TWIG);

        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('<link rel="stylesheet" href="/a.css"></head>', $html, 'A slot reaches the head.');
        self::assertStringContainsString('<nav>side</nav>', $html);
        self::assertStringContainsString('<p>Body</p>', $html);
    }

    public function testAPageRendersThroughLatte(): void
    {
        self::assertStringStartsWith('<!doctype html>', $this->latte(self::LATTE));
    }

    public function testBothEnginesAssembleTheSameDocument(): void
    {
        self::assertSame(
            $this->twig(self::TWIG),
            $this->latte(self::LATTE),
            'Both compile to the same renderer, so the assembled document must match byte for byte.',
        );
    }

    private static function defaults(): PageDefaults
    {
        return new PageDefaults(appTitle: 'Course Catalog', appSubtitle: 'Indiana University');
    }

    private function twig(string $template): string
    {
        $registry = Rivet::registry();
        $renderer = new Renderer($registry, pageDefaults: self::defaults());

        $twig = new Environment(new ArrayLoader(['t' => $template]));
        $twig->addExtension(new TwigExtension($registry));
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
        ]));

        return $twig->render('t');
    }

    private function latte(string $template): string
    {
        $registry = Rivet::registry();

        $latte = new LatteEngine();
        $latte->setLoader(new StringLoader(['t' => $template]));
        $latte->addExtension(new LatteExtension($registry, new Renderer($registry, pageDefaults: self::defaults())));

        return $latte->renderToString('t');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Component/Page/PageEngineTest.php`
Expected: FAIL — `Twig\Error\SyntaxError: Unknown "rvt_page" tag`

- [ ] **Step 3: Register the components**

In `src/Rivet.php`, add the imports and registry entries, keeping both lists alphabetical:

```php
use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Component\Page\PageBreadcrumbs;
use Guild\Rivet\Component\Page\PageScripts;
use Guild\Rivet\Component\Page\PageSidebar;
use Guild\Rivet\Component\Page\PageStyles;
```

and, in the `registry()` array after `Pagination::class`:

```php
            Page::class,
            PageBreadcrumbs::class,
            PageScripts::class,
            PageSidebar::class,
            PageStyles::class,
```

- [ ] **Step 4: Run tests and the full check**

Run: `vendor/bin/phpunit tests/Component/Page/PageEngineTest.php` — Expected: PASS
Run: `composer check` — Expected: all green. `CrossEngineParityTest::testEveryRegisteredComponentIsExposedByBothEngines` covers the new registrations automatically.

- [ ] **Step 5: Verify the whole-page parity case by eye**

Run: `composer components | grep rvt_page`
Expected: five rows — `rvt_page` (block) and the four slots (block).

- [ ] **Step 6: Commit**

```bash
git add src/Rivet.php tests/Component/Page/PageEngineTest.php
git commit -m "Register the page components and prove both engines agree

A page written the same way in Twig and Latte must assemble byte-identical
documents, which is the guarantee the rest of the library already keeps for
individual components and now keeps for whole pages."
```

---

### Task 7: Framework integration

**Files:**
- Modify: `../framework/src/ApplicationBuilder.php`
- Modify: `../framework/src/ServiceProvider/RivetServiceProvider.php`
- Modify: `../framework/AGENTS.md`

**Interfaces:**
- Consumes: `PageDefaults` (Task 1), `Renderer::__construct(..., ?PageDefaults)` (Task 2)
- Produces: `ApplicationBuilder::addRivet(?PageDefaults $pageDefaults = null): self`

**Before starting:** run `composer analyse` in `../framework` and record the error count. The
bar in that repo is **no new failures**, not zero — it has a documented red baseline.

- [ ] **Step 1: Update `RivetServiceProvider` to accept defaults**

```php
    public function __construct(
        private readonly TemplateEngine $templateEngine,
        private readonly ?PageDefaults $pageDefaults = null,
    ) {
    }
```

and in `boot()`, pass them to the renderer:

```php
        $renderer = new Renderer($registry, pageDefaults: $this->pageDefaults);
```

Add `use Guild\Rivet\Page\PageDefaults;`.

- [ ] **Step 2: Update `addRivet()`**

```php
    public function addRivet(?PageDefaults $pageDefaults = null): self
    {
        $engine = $this->app->getTemplateEngine();

        if ($engine === null) {
            throw new ConfigurationException(
                'addRivet() needs a template engine to register the components with. Call addTemplateEngine() first.'
            );
        }

        $this->app->addServiceProvider(new RivetServiceProvider($engine, $pageDefaults));

        return $this;
    }
```

Add `use Guild\Rivet\Page\PageDefaults;`.

- [ ] **Step 3: Verify by rendering a page through the framework**

Create `/tmp/rvt-page-app/templates/page.html.twig`:

```twig
{% rvt_page title="It works" heading="It works" %}<p>Rendered through the framework.</p>{% endrvt_page %}
```

Run:

```bash
cd ../framework && php -r '
require "vendor/autoload.php";
use Guild\Framework\{Application, TemplateEngine, View};
use Guild\Rivet\Page\PageDefaults;
$app = Application::configure("/tmp/rvt-page-app")
    ->addTemplateEngine(TemplateEngine::Twig)
    ->addRivet(new PageDefaults(appTitle: "Demo"))
    ->enableAutoWiring()->create();
echo substr($app->get(View::class)->render("page.html.twig"), 0, 120), "\n";'
```

Expected: output begins `<!doctype html><html lang="en"><head>…<title>It works · Demo</title>`

- [ ] **Step 4: Confirm no new static-analysis failures**

Run: `cd ../framework && composer analyse`
Expected: the same error count recorded before Step 1.

- [ ] **Step 5: Document the parameter in `framework/AGENTS.md`**

In the `addRivet()` bullet under Bootstrap flow, append:

> It optionally takes a `PageDefaults`, which the `rvt_page` layout component requires.
> Passed explicitly rather than read from a config file: an *optional* config file is not
> a pattern this repo has, and an application that wants its defaults in a file can
> `require` one at the call site.

- [ ] **Step 6: Commit (in the framework repo)**

```bash
cd ../framework
git add src/ApplicationBuilder.php src/ServiceProvider/RivetServiceProvider.php AGENTS.md
git commit -m "Let addRivet() carry page defaults

The rvt_page layout needs application-wide values - app title, navigation,
footer links - and they reach it through the renderer. Passed explicitly
rather than read from a config file, because an optional config file is not
a pattern this repo has."
```

---

### Task 8: Documentation and demo

**Files:**
- Modify: `README.md`
- Modify: `examples/public/index.php`
- Create: `examples/templates/page.html.twig`, `examples/templates/page.latte`
- Delete: `examples/templates/demo.html.twig`, `examples/templates/demo.latte`

**Interfaces:**
- Consumes: everything above

- [ ] **Step 1: Rewrite the asset section of `README.md`**

Replace the `### Rivet's own assets` section. It currently states the application supplies
Rivet; that is now only true when the layout is unused or assets are switched off. The
replacement must:

- say that `rvt_page` emits Rivet's stylesheet and script by default;
- say that an application not using `rvt_page` still supplies them itself, and keep the
  existing snippet for that case;
- point at `RivetAssets` for pinning a version or self-hosting, **without restating the
  version number** — `RivetAssets` and `Rivet::VERSION` are where a release is named, and
  a number repeated in prose will drift.

- [ ] **Step 2: Add a layout section to `README.md`**

After `## Setup`, document `PageDefaults`, the three `PageLayout` values, the four slots
and a worked Twig and Latte example. Reuse the examples from `docs/specs/2026-09-14-page-layout-design.md`.

- [ ] **Step 3: Convert the demo templates to use the layout**

Move the component showcase into the page body. `examples/templates/page.html.twig` begins:

```twig
{% rvt_page title="Components" heading="Components" layout="sidebar" %}
  {% rvt_page_sidebar %}
    {{ rvt_sidenav(label: 'Sections', items: [
        {label: 'Buttons', href: '#buttons'},
        {label: 'Forms', href: '#forms'},
        {label: 'Navigation', href: '#nav'}
    ]) }}
  {% endrvt_page_sidebar %}
```

and ends `{% endrvt_page %}`. The existing component showcase becomes the body, with the
`rvt_header`, `rvt_container` and `rvt_footer` calls removed — the page supplies all three.
Mirror the same changes in `examples/templates/page.latte`.

- [ ] **Step 4: Simplify the demo front controller**

`examples/public/index.php` no longer emits `<!doctype>`, `<head>`, `<body>` or the Rivet
asset tags — the page does. It keeps only: the two render closures, the parity comparison,
and the engine switch. The parity banner must now be injected into the rendered document
rather than wrapped around it; insert it immediately after `<body class="rvt-layout">`
using `str_replace` with a count of 1.

The two render closures pass defaults:

```php
$defaults = new PageDefaults(
    appTitle: 'Rivet for PHP',
    appSubtitle: 'Indiana University',
    navItems: [
        ['label' => 'Components', 'href' => '#', 'current' => true],
    ],
);
```

- [ ] **Step 5: Verify the demo renders and the engines still agree**

Run: `php -S localhost:8080 -t examples/public` and open `http://localhost:8080`
Expected: a complete Rivet page with header, sidebar, components and footer; the banner
reports that both engines produced identical markup. Check `?engine=latte` too.

- [ ] **Step 6: Run the full check and commit**

```bash
composer check
git add README.md examples
git rm examples/templates/demo.html.twig examples/templates/demo.latte
git commit -m "Use the page layout in the demo, and correct the asset documentation

The demo front controller no longer hand-writes the doctype, head, body and
Rivet asset tags - the page component supplies all of them, which is the
point of it and shrinks the file considerably.

The README said asset delivery was out of scope and the application supplies
Rivet. That is now true only when the layout is unused or assets are switched
off, so the section is rewritten rather than left contradicting RivetAssets.
It points at RivetAssets for versions rather than restating a number that
would drift."
```

---

## Self-Review

**Spec coverage.** Every section of `docs/specs/2026-09-14-page-layout-design.md` maps to a
task: value objects → Task 1; `StartsRender`, `PageLayout`, context plumbing → Task 2;
document assembly, title rules, description precedence, heading-band condition, missing
defaults → Task 3; the four slots and their routing → Task 4; all three layout structures
and the sidebar-under-single-column error → Task 5; registration and parity → Task 6;
framework integration → Task 7; README correction and demo conversion → Task 8.

**Additions beyond the spec, both deliberate.** `Exception\ConfigurationException` — the
spec described the error but not its type, and reusing `InvalidArgumentException` would
misdescribe a setup problem as a bad value. `RenderContext::isEmpty()` — needed so
`Renderer` can reject a nested document without knowing what a `Page` is.

**Type consistency.** `Page`'s registration methods are named `addStyles`, `addScripts`,
`setSidebar`, `setBreadcrumbs` in Tasks 3, 4 and 5 alike — `add*` where a page may have
several, `set*` where it has one. `PageSlot::give()` is the single abstract hook. `Renderer`
and `RenderContext` both take `?PageDefaults $pageDefaults` as a named parameter throughout.
