# AGENTS.md: guild/rivet

> For maintainers of guild/rivet and their agents. App developers start at [docs/index.md](docs/index.md).

The [workspace handbook](../AGENTS.md) covers cross-repo workflow and conventions.

guild/rivet renders IU Rivet Design System markup from plain PHP component objects, with thin Twig and
Latte extensions that expose them as tags. It targets the Rivet release in `Rivet::VERSION`.

## Verify

`composer check` must pass. It passes on a clean checkout, so any failure is yours. `composer components`
lists every registered component and its parameters.

## Keeping docs current

Every code change gets a docs check in the same commit. Check README, `docs/`, AGENTS.md, the docblocks
of public classes and the comments at the changed code for anything the change makes wrong or incomplete:

- Update a page that describes the changed behavior.
- If no page covers new public behavior, add one to `docs/` and list it in `docs/index.md`.
- If a maintainer needs a fact the code doesn't show, add a comment where the code is.
- If the behavior is gone, remove its page and its index entry.

Then check the pages that link to a changed page for text that's now wrong, and link a new page from
related pages. A non-obvious choice gets a decision record in `docs/decisions/`.

Public behavior here includes every component's tag name, body and required parent
([Components](docs/reference/components.md)), the argument rules, `rvt_page`'s layouts and slots, and the
exceptions. Component classes, `PageDefaults` and `RivetAssets` keep their API reference in docblocks, so
a change to them updates the docblock. A new component goes in `Rivet::registry()` and the components
page.

## Conventions

- Generate markup in PHP through `Html`, never from template files.
- Keep each component's `rvt-*` class literals in one place, normally a `BLOCK` constant.
- Model each Rivet modifier axis as its own enum, composed with `Html\Modifier`, never one enum per compound class.
- Template argument names are `snake_case`; `ComponentFactory` maps them to `camelCase` parameters.
- A component that can't render correct markup throws instead of emitting something subtly wrong.
- Take every class name, attribute and SVG path from Rivet's published docs, never from memory.
- `twig/twig` and `latte/latte` stay in `suggest` and `require-dev`, never `require`.

Latte's `FileLoader` refuses templates outside the app's own `templates/` directory, so a vendor package
can't ship template files; generating markup in PHP also keeps one source of truth for both engines.
Keeping class literals in one place per component makes a Rivet 3 block rename a one-file change.

guild/framework constructs `Renderer` and uses `PageDefaults` and `NavigationProvider` directly, so a
signature change there needs a matching framework change.

## Tests

- Use `symfony/dom-crawler` for semantic assertions where exact markup isn't the point.
- Add a case to `tests/CrossEngineParityTest.php` for any component with unusual tag syntax.
- Don't use PHPUnit mocks.

The parity test compares component markup only. Template interpolation differs on purpose: Twig escapes
with `ENT_QUOTES` and Latte with `ENT_NOQUOTES`, and neither engine's behavior should be overridden.

## Gotchas

- Which ARIA attributes a component writes differs per component; read its docblock before adding one.
- `Renderer::render()` must keep trimming the captured body, or the engines can't match byte for byte.
- Engine nodes pop the context stack in a `finally`; a leaked frame corrupts every later render.
- Twig nodes need `#[\Twig\Attribute\YieldReady]` and must `yield`, never `echo`.
- A Latte node's `getIterator()` is a by-reference generator, where `yield from []` is fatal.
- Only `rvt_page` asks the `NavigationProvider`, once per page render; keep it that way.
- `SvgIcon` holds path data copied from Rivet; re-fetch a path instead of editing it.

The comment at each gotcha's code explains the reason.

`rvt_page`'s inline `Rivet.init()` script is blocked by a strict Content-Security-Policy, and no nonce
support exists yet. The open question and the options considered are in the workspace repo's
`docs/archive/rivet/open-questions/2026-09-15-csp-nonce.md`.
