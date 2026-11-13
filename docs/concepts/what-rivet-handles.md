# What Rivet handles

> For PHP developers who want to know what guild/rivet's components do beyond printing Rivet's markup.

The [Rivet Design System](https://rivet.iu.edu) publishes markup to copy into a page. Copied markup
breaks in ways that are easy to miss: repeated IDs, missing accessible names and ARIA attributes that
conflict with Rivet's script. guild/rivet's components generate that markup and its wiring for each use.

guild/rivet emits Rivet 2.9.1 markup, recorded in `Rivet::VERSION`. It ships no JavaScript; Rivet's own
script runs every interactive component.

## Identifiers

A Rivet dialog uses the same ID in up to six places, a file input in five, and every form control needs
its own. Rivet's examples hard-code those IDs, such as `dialog-title`, so two copied examples on one page
already clash. guild/rivet generates every ID for each render and derives the related ones from it.

## ARIA attributes

Some Rivet components expect you to write ARIA attributes, and others have Rivet's script write them.
A disclosure takes `aria-expanded` as its starting state. An accordion, dropdown or tab set must have
none, because Rivet's script adds roles, `aria-expanded` and `aria-controls` itself, and attributes in
the markup conflict with them. Each component writes only the attributes Rivet expects of it.

## Accessible names

A component that would have no accessible name throws `InvalidArgumentException` instead of rendering.
Examples are a button with no text, a switch with no label, a segmented group with no name and an avatar
image with no alt text.

## Form fields

`rvt_form_field` gives its control one ID, ties the label to it, and points `aria-describedby` at the
helper text and errors it renders. A field with errors marks its control invalid.

## Twig and Latte

Both engines render through the same PHP objects, so a component's markup is identical in each. Whole
pages differ only in whitespace between elements, because the engines treat the newline after a tag
differently. Templates escape interpolated values by each engine's own rules.

## Rivet 3

Rivet 3 is in development. It keeps the `rvt-` classes and the `data-rvt-*` attributes, so the ID wiring
carries over, but it changes button modifiers and some block names and rewrites the header and footer.
guild/rivet will need changes for it, and supports only Rivet 2 today.

## Related

- [Components](../reference/components.md) lists the components and their exceptions.
- [Use components](../guides/use-components.md) shows how to write them.
