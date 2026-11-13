# Set up Rivet

> For PHP developers adding guild/rivet's components to an app.

This guide shows you how to register the components with your template engine and load Rivet's CSS and
JavaScript.

## Before you start

guild/rivet is installed; the [README](../../README.md#install) shows how. For templates, your app also
needs `twig/twig` `^3.28` or `latte/latte` `^3.1`.

## Set up in a Guild app

Call `addRivet()` after `addTemplateEngine()` in `config/app.php`:

```php
return Application::configure($basePath)
    ->addRouting()
    ->addTemplateEngine(TemplateEngine::Twig)
    ->addRivet()
    ->enableAutoWiring()
    ->create();
```

`addRivet()` reads the engine you chose, so calling it first throws `ConfigurationException`. It takes
your `PageDefaults` as its argument; [Build a page](build-a-page.md) describes them. The framework builds a
new renderer for every request, so you don't call `reset()`.

## Set up Twig

Register the extension and its runtime:

```php
use Guild\Rivet\Rivet;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Twig\RivetExtension;
use Guild\Rivet\Twig\RivetRuntime;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

$registry = Rivet::registry();
$renderer = new Renderer($registry, pageDefaults: $defaults);

$twig->addExtension(new RivetExtension($registry));
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
]));
```

## Set up Latte

Register the extension:

```php
use Guild\Rivet\Latte\RivetExtension;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Rivet;

$registry = Rivet::registry();
$renderer = new Renderer($registry, pageDefaults: $defaults);

$latte->addExtension(new RivetExtension($registry, $renderer));
```

## Start each request

Outside a Guild app, if a request renders components without `rvt_page`, call `$renderer->reset()` once
at the start of the request. `rvt_page` resets the renderer itself. Without a reset, generated
identifiers stay unique but keep growing for the life of the PHP process, so the same page renders
differently each time.

## Load Rivet's CSS and JavaScript

A page built with `rvt_page` loads Rivet's stylesheet, its script and the icons package from unpkg. To
change the version, self-host a file or drop the icons, pass a `RivetAssets` as `assets:` in
`PageDefaults`. Its docblock lists each option.

If you don't use `rvt_page`, add Rivet's tags to your layout yourself:

```html
<link rel="stylesheet" href="https://unpkg.com/rivet-core@2.9.1/css/rivet.min.css">
<script src="https://unpkg.com/rivet-core@2.9.1/js/rivet.min.js"></script>
<script>Rivet.init()</script>
```

If you use `rvt_icon`, add the icons package too:

```html
<link rel="stylesheet" href="https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icon-element.css">
<script type="module" src="https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icons.js"></script>
```

`Rivet.init()` also sets up components added to the page later, such as by htmx.

If your app sends a Content-Security-Policy whose `script-src` blocks inline scripts, the browser blocks
`rvt_page`'s `Rivet.init()`, and interactive components do nothing. To work around it, pass
`RivetAssets(enabled: false)` and load Rivet with tags your policy allows.

## Related

- [Build a page](build-a-page.md) shows how to write a page with `rvt_page`.
- [Use components](use-components.md) shows how to write the tags.
