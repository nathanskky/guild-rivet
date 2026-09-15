# Handoff: wire the page layout into guild/starter

**Status:** not started. Written for a fresh session to pick up.
**Work lands in:** `~/Desktop/guild/starter` (a different repo from this one).
**Depends on:** `guild/rivet` and `guild/framework`, both pushed to `develop`.

## Goal

Make `guild/starter` a working demonstration of the whole stack: an application that
renders a real Rivet page through the framework, so someone copying the starter gets a
correct IU page instead of `<h1>{{ title }}</h1>`.

## Read this first — starter is currently broken

`starter` cannot resolve its dependencies right now. Verified:

```
$ composer update guild/framework --dry-run
guild/framework dev-develop requires guild/rivet dev-develop
  -> could not be found in any version
```

**Composer repositories are not transitive.** `framework` declares where to find
`guild/rivet`, but a consuming application does not inherit that — `starter` has to
declare the same VCS repository in its own `composer.json`. Adding `guild/framework`'s
new dependency broke `starter` the moment it was pushed, and nobody noticed because
`starter/vendor/` still holds a framework snapshot from before that change.

`composer.lock` exists but is gitignored, so it will not save a fresh clone.

**This is the first thing to fix, and it is a prerequisite for everything else.** Add to
`starter/composer.json`, alongside the two existing VCS entries:

```json
{ "type": "vcs", "url": "https://github.com/nathanskky/guild-rivet.git" }
```

Then `composer update guild/framework` should pull `guild/rivet` transitively. Confirm
`vendor/guild/rivet/src/Page/` exists afterwards.

Whether `starter` should *also* require `guild/rivet` directly is a judgement call — it
does this already for `guild/access` (`^1.0`), which `framework` also requires, on the
grounds that the app uses it directly. Templates here will use `rvt_*` tags directly, so
the same argument applies. A direct requirement also means a version bump must satisfy
two constraints, which is the documented trade-off in `starter/AGENTS.md`.

## Current state, verified

`config/app.php` — the whole application definition:

```php
return Application::configure($basePath)
    ->addRouting()
    ->addIlluminateDatabase()
    ->addTemplateEngine(TemplateEngine::Twig)
    ->enableAutoWiring()
    ->create();
```

`routes/routes.php` maps `GET /` to `ExampleController::index`, which renders
`example/index.html.twig` with `title` and `message`. That template is two lines:

```twig
<h1>{{ title }}</h1>
<p>{{ message }}</p>
```

`templates/` contains nothing else. `public/css/` and `public/js/` exist and are empty.
There are no tests; `composer test` fails by design on an empty suite, and `starter` has
no PHPStan at all.

## What to do

1. **Fix the dependency** as above. Nothing else works until this does.
2. **Add `->addRivet(...)` to `config/app.php`**, after `addTemplateEngine()`. It throws
   `ConfigurationException` if called before, because it reads which engine was chosen.
3. **Decide where `PageDefaults` lives** — see Decisions below.
4. **Rewrite `templates/example/index.html.twig`** to use `rvt_page`, with enough
   components to show the stack working — a heading, some body content, and at least one
   interactive component so that Rivet's JavaScript is visibly doing something.
5. **Verify by running the app**, not by reading it. `starter/docker/` has a
   compose file; `php -S localhost:8080 -t public` is faster for a smoke test. Confirm a
   complete document, Rivet's CSS loading, and an interactive component actually working
   in a browser.

## Decisions for whoever picks this up

**Where does `PageDefaults` go?** `starter/AGENTS.md` states its most important
convention: *config is executable PHP that returns objects*, with a table mapping each
file to what it must return. A `config/rivet.php` returning a `PageDefaults`, loaded as
`->addRivet(require __DIR__ . '/rivet.php')`, fits that convention exactly and gives
someone copying the starter an obvious place to put their own app title and navigation.
The alternative is constructing it inline in `config/app.php`, which is fewer files but
buries app-wide configuration inside the builder chain. **Recommend the separate file**,
and add a row to that table in `starter/AGENTS.md`.

**Does the Rivet example replace the existing example, or sit beside it?**
`starter/AGENTS.md` is explicit that `src/Example/` "is not a pattern to copy: it exists
to keep the example code in one identifiable place so it is easy to delete." Converting
the existing example keeps that property. Adding a second example alongside it does not,
and leaves two things to delete. **Recommend converting the existing one.**

**Should the starter ship a sidenav?** `rvt_page` supports three layouts. The
single-column one is the simplest honest default and needs no sidebar content invented
for it. A sidebar example demonstrates more but puts fake navigation in a template people
copy. **Recommend `single_column`**, with a comment noting the other two exist.

**Assets: CDN or self-hosted?** `RivetAssets` defaults to unpkg, which makes the starter
work immediately with no build step. An IU deployment may want self-hosting. The starter
should probably take the default and say so, rather than ship a build pipeline it does
not otherwise have — `starter` has no `package.json` and no asset tooling at all today.

## Conventions to respect

- Direct commits to `develop` are permitted for this sole developer; no feature branch.
- Commit messages carry **no AI attribution** of any kind — no `Co-Authored-By`, no
  "generated with" footer. Hard rule across this workspace.
- `composer.lock` is gitignored in every one of these repos. Do not commit one.
- **Never hand-edit `starter/vendor/guild/*`** — the next `composer install` reverts it.
- If a path repository is used for local iteration, revert it before committing.
- Style is Laravel Pint, PSR-12; run `composer format`, do not fight its output.
- Documentation states what is true, not what changed.

## Do not

- Do not add tests to `starter` to "fix" the failing `composer test`. The empty suite is
  deliberate and documented.
- Do not add PHPStan to `starter`. Its absence is a recorded decision, not an oversight.
- Do not touch the uncommitted working-tree changes already in that repo
  (`docker/auth_cas.conf`, `docker/docker-compose.yml`, `public/.htaccess`). They are not
  part of this task.

## Reference

- `guild/rivet` `README.md` — setup, the three layouts, the four slots, worked examples.
- `docs/specs/2026-09-14-page-layout-design.md` in this repo — why the layout is a
  component rather than template inheritance.
- `docs/open-questions/2026-09-15-csp-nonce.md` — relevant only if the starter is meant
  to demonstrate a strict Content-Security-Policy, which it currently is not.
- `composer components` in the `guild/rivet` repo lists every available tag.
