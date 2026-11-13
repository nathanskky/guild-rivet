# Use components

> For PHP developers writing Rivet components in Twig, Latte or plain PHP.

This guide shows you how to write a component as a tag or a function, pass its arguments, and render it
from PHP.

## Before you start

The components are set up in your template engine, as in [Set up Rivet](set-up-rivet.md). For plain PHP,
nothing needs setting up.

## Write a component

Components are tags: `snake_case` in Twig and `camelCase` in Latte. A component that takes no body is a
single tag. A component that takes a body is a paired tag:

```twig
{% rvt_badge text="New" %}

{% rvt_card raised=true %}
  {% rvt_card_image src="/hero.webp" alt="" %}
  {% rvt_card_body title="Campus life" title_href="/life" heading_level=2 %}
    <p>Each of our nine campuses…</p>
  {% endrvt_card_body %}
{% endrvt_card %}
```

```latte
{rvtBadge text: 'New'}

{rvtCard raised: true}
  {rvtCardImage src: '/hero.webp', alt: ''}
  {rvtCardBody title: 'Campus life', titleHref: '/life', headingLevel: 2}
    <p>Each of our nine campuses…</p>
  {/rvtCardBody}
{/rvtCard}
```

When a body would only be a label, use the short form. In Twig, every component is also a function. In
Latte, a component that takes a body also has a self-closing tag:

```twig
{{ rvt_button('Save changes', purpose: 'danger') }}
```

```latte
{rvtButton 'Save changes', purpose: danger /}
```

The [component reference](../reference/components.md) says which components take a body. Both engines
produce the same markup for every component.

## Pass arguments

- Write argument names in `snake_case`, such as `helper_text`; they map to the `camelCase` parameter.
- Pass the first argument by position if you like: `rvt_badge('New', style: 'info')`.
- Pass an enum by its string value, such as `style: 'danger'`.
- Any other argument becomes an HTML attribute, with `_` changed to `-`: `data_testid` emits `data-testid`.
- A `class` argument adds your classes after the component's own.

Twig can't read a hyphen inside a tag, which is why argument names use underscores in both engines.

## Render from PHP

To render a component without a template engine, construct it and call `render()`:

```php
use Guild\Rivet\Component\Alert;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Render\RenderContext;

echo new Alert('Maintenance', style: AlertStyle::Warning)
    ->render(new RenderContext(), '<p>Back on Tuesday.</p>');
```

In PHP, arguments use the constructor's `camelCase` names and real enum cases.

## Related

- [Components](../reference/components.md) lists every component and the exceptions they throw.
- [What Rivet handles](../concepts/what-rivet-handles.md) explains what the components wire up for you.
