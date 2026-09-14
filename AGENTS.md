# AGENTS.md

Guidance for AI coding agents (and new humans) working in this repository.

## What this is

`guild/rivet` — IU [Rivet Design System](https://rivet.iu.edu) components for PHP.
Components are plain PHP objects that render Rivet markup; thin Twig and Latte extensions
expose them as native tags. Namespace `Guild\Rivet\`, autoloaded from `src/`. Consumed as
a Composer dependency; not runnable on its own.

- **PHP:** `~8.5.0`
- **Targets Rivet 2.9.1.** `Rivet::VERSION` records it.
- **`composer.lock` is gitignored** here, so there is no lock to keep in sync.
- **Ships no JavaScript.** Rivet's own script drives every interactive component.

> **`README.md` is the authoritative reference for this library's public API** — setup,
> argument conventions, the component table, and the error types. Read it before changing
> anything under `src/Component/`. This file covers *developing* the package; the README
> covers *using* it.

## Sibling packages

These repos are developed side by side but are **independent git repos**. There is no root
`composer.json` and no root git repository. Do not invent root-level tooling or a shared
root autoloader.

| Package | Namespace | Role |
|---|---|---|
| `guild/rivet` *(this one)* | `Guild\Rivet\` | Rivet Design System components |
| `guild/framework` | `Guild\Framework\` | Application kernel / DI container. Will expose this package via `ApplicationBuilder::addRivet()` |
| `guild/access` | `Guild\Access\` | IU Login (OIDC) authentication library |
| `guild/starter` | `Guild\Starter\` | Runnable example app |
| `iu/notifications` | `IU\Notifications\` | IU Notifications API client. Fully independent |

This package has **no first-party dependencies**, and `twig/twig` and `latte/latte` are
deliberately *not* required — only suggested, and installed as dev dependencies. The
engine extension classes ship here but are instantiated only by an application that has
that engine. **Never move either into `require`.**

## Verify your change

```bash
composer install
composer test          # phpunit, suite "guild-rivet"
composer analyse       # phpstan, level max, scoped to src/
composer format:check  # pint, PSR-12 style check (writes nothing)
composer check         # all three; stops at the first failure
composer components    # list every registered component and its parameters
```

**This package is green and must stay that way.** Unlike `framework`, `starter` and
`notification`, there is no known-red baseline here — any failure is yours. PHPStan runs
at `max`, the strictest setting in the workspace.

## Architecture

```
src/
  Component/      one class per component; Card/, Dialog/, Form/, Grid/, Accordion/, Tabs/
    Internal/     SvgIcon — the icons components embed in their own chrome
  Html/           Html (element builder), Attributes, Modifier
  Render/         Renderer, RenderContext, IdGenerator, ComponentFactory, ComponentRegistry
  Enum/           one enum per variant axis
  Exception/
  Twig/           RivetExtension, token parser, nodes, runtime
  Latte/          RivetExtension, nodes
```

**Markup is generated in PHP, never from template files.** Latte's `FileLoader` throws for
any template outside the application's own `templates/` directory, so a vendor package
cannot ship `.latte` files an app can load. Generating in PHP also gives one source of
truth instead of two that drift.

**`Html` is the element builder every component renders through.** `text()` escapes,
`html()` does not — the distinction is in the method name so an unescaped value is always
a deliberate choice. Null and false attributes and null children drop out, which keeps
conditional markup to one expression.

**`RenderContext` is a stack of open components plus an id generator.** It is what lets a
child read from its parent — a dialog close button deriving the id it must reference, a
card body confirming it is inside a card. One instance per render, never global.

**Both engines compile to the same `Renderer`.** Leaf components become functions and
single tags; block components become paired tags whose body is captured as a string.
Neither engine ever sees a template file.

**A component registered in `Rivet::registry()` is automatically available in both
engines.** A test asserts that, so a component cannot be wired into only one.

## Conventions

**This repo is formatted with Laravel Pint (PSR-12 preset), configured in `pint.json`.**
Run `composer format` to apply it. Its rules are authoritative for anything it enforces —
don't hand-fix a style issue Pint would catch.

- **Every `rvt-*` class literal lives in one place per component**, normally a `BLOCK`
  constant. Rivet 3 renames several blocks, and this keeps that a single-file change.
- **Never let one enum member encode a compound class name.** Rivet composes
  `rvt-button--danger-secondary` from two independent axes; model them as two enums and
  compose with `Html\Modifier`. A flat enum mirroring class names invalidates every call
  site when Rivet renames one.
- **Argument names reaching templates are snake_case**, mapped to camelCase parameters by
  `ComponentFactory`. Twig cannot lex a hyphen in tag syntax at all.
- **Fail loudly.** A component that cannot render correct markup throws rather than
  emitting something subtly wrong: no accessible name, an out-of-range value, a
  combination Rivet has no class for.
- **Do not invent markup.** Every class name, attribute and SVG path here was read from
  Rivet's published documentation. If you need markup you do not have, fetch it from
  rivet.iu.edu rather than reconstructing it from memory.

**Test conventions** follow `access/tests/`, the workspace reference suite: `final class
XxxTest extends TestCase`, attributes not annotations, `self::assert*` with an intent
message, `public static` data providers returning `iterable`, private fixture helpers, no
mocks. On top of that:

- **`symfony/dom-crawler`** for semantic assertions where exact markup is not the point.
- **`spatie/phpunit-snapshot-assertions`** is available for golden files.
- **`tests/CrossEngineParityTest.php` is the guard that keeps the two engines honest.**
  Add a case there for any component whose tag syntax is unusual. It compares *component*
  markup only — template interpolation is excluded on purpose, because Twig escapes with
  `ENT_QUOTES` while Latte uses `ENT_NOQUOTES` and rewrites braces, which is engine
  behaviour neither should override.

## Landmines

- **Which ARIA we author differs per component, and getting it wrong is silent.** A
  disclosure takes `aria-expanded` because Rivet documents it as an initial state. An
  accordion, dropdown and tab set must be given **none** — Rivet's JavaScript generates
  roles, `aria-expanded`, trigger ids and `aria-controls` at init, and anything authored
  here is overwritten or conflicts. Check the component's docblock before adding an
  attribute.
- **`Renderer::render()` trims the captured body, and must keep doing so.** Twig swallows
  one newline after `%}`; Latte relocates leading indentation into the following element,
  across the capture boundary. Without the trim the two engines cannot produce identical
  bytes for any indented template, and the parity test becomes impossible.
- **Engine nodes pop the context stack in a `finally`.** Twig memoises its runtime for the
  life of the Environment and Latte holds the provider for the life of the Engine, so one
  leaked frame corrupts every later render in the process.
- **Custom Twig nodes must carry `#[\Twig\Attribute\YieldReady]` and emit `yield`, never
  `echo`.** Twig 3.28 deprecates both omissions, and `Compiler::checkForEcho()` scans the
  emitted code.
- **A Latte node's `getIterator()` is a by-reference generator**, where `yield from []` is
  a fatal error. Use Latte's `false && yield;` idiom only when there is nothing else to
  yield.
- **`Renderer::reset()` has to be called by the integration**, once per page. Neither
  engine exposes a reliable "top-level render started" hook. Identifiers stay unique
  without it but climb for the life of the process.
- **`SvgIcon` is `@internal` and holds real path data copied from Rivet.** Do not edit a
  path by hand; re-fetch it.

## Branching and pull requests

The workspace `CLAUDE.md` currently permits committing directly to `develop` in these
repos, for the sole developer. Absent that override the normal flow applies: work on a
feature branch and open a pull request against `develop`.

### Branch and release model

```
feature branch  --PR-->  develop  --PR-->  main  --> tag (release)
```

`develop` accumulates day-to-day work. `main` is the released state, merged from `develop`
via its own pull request. **Tags live on `main`, never on `develop`.**

## Getting a change to consumers

Consumers pull this package as a downloaded zipball, so editing `src/` here changes
nothing in a consuming application until the change is published.

If a consumer requires a tag constraint such as `^1.0`, **merging to `develop` is not
enough** — a change becomes visible only once tagged, and tags are cut from `main`:

```bash
git checkout main && git pull
git tag <next-version> && git push --tags
```

For a local iteration loop, temporarily add a path repository to the consumer's
`composer.json` above its VCS entries, then `composer update guild/rivet`:

```json
{ "type": "path", "url": "../rivet", "options": { "symlink": true } }
```

**Revert that before committing.** It is a local-only convenience and will break the build
for anyone whose checkout has no sibling `../rivet`.
