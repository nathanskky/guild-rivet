# guild/rivet

IU [Rivet Design System](https://rivet.iu.edu) components for PHP, with Twig and Latte tags. Write
`{% rvt_dialog title="Confirm" %}` instead of copying forty lines of markup, and get the IDs, ARIA
attributes and accessible names right for each use.

guild/rivet emits Rivet 2.9.1 markup and ships no JavaScript; Rivet's own script runs the interactive
components.

## Install

guild/rivet requires PHP 8.5. For templates, your app also needs `twig/twig` `^3.28` or `latte/latte`
`^3.1`.

Add this package's VCS repository to `repositories` in your app's `composer.json`:

```json
{ "type": "vcs", "url": "https://github.com/nathanskky/guild-rivet.git" }
```

Then install it:

```bash
composer require guild/rivet
```

To see the components running with Rivet's CSS and JavaScript, run
`php -S localhost:8080 -t examples/public` in a checkout of this repository.

## Start here

[Set up Rivet](docs/guides/set-up-rivet.md) shows you how to register the components with your template
engine, in a Guild app or on its own.

## All docs

The [docs index](docs/index.md) lists every guide, concept and reference page.
