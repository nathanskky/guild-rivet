# guild/rivet docs

> For PHP developers building IU pages with Rivet components in Twig, Latte or plain PHP.

## Guides

- [Set up Rivet](guides/set-up-rivet.md) — read when adding the components to a Guild app, a Twig or Latte app, or a page without `rvt_page`.
- [Build a page](guides/build-a-page.md) — read when writing a whole page with `rvt_page`: app-wide defaults, navigation, layouts and slots.
- [Use components](guides/use-components.md) — read when writing a component tag or function, passing arguments, or rendering from PHP.

## Concepts

- [What Rivet handles](concepts/what-rivet-handles.md) — read when you need to know what the components do for you, or which Rivet release they target.

## Reference

- [Components](reference/components.md) — read when looking up a component's name, whether it takes a body, the parent it needs, or the exceptions it throws.

Each component class documents its parameters in docblocks, and `composer components` lists every
parameter.
