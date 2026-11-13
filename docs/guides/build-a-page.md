# Build a page

> For PHP developers writing a whole IU page with `rvt_page`.

This guide shows you how to set your app's page defaults, write a page with `rvt_page`, change the
navigation per viewer, and choose a layout.

## Before you start

The components are set up in your template engine, as in [Set up Rivet](set-up-rivet.md).

## Set the page defaults

`rvt_page` reads everything that's the same on every page from a `PageDefaults`. Create one with at least
your app's title:

```php
use Guild\Rivet\Page\PageDefaults;

$defaults = new PageDefaults(
    appTitle: 'Course Catalog',
    navItems: [
        ['label' => 'Courses', 'href' => '/courses'],
        ['label' => 'About', 'href' => '/about'],
    ],
);
```

In a Guild app, pass it to `addRivet($defaults)`. Otherwise pass it to the `Renderer` as
`pageDefaults:`. `PageDefaults`'s docblock lists the other fields, such as the subtitle, search form,
footer links and container size. Without a `PageDefaults`, `rvt_page` throws `ConfigurationException`.

## Write the page

Wrap the page's template in `rvt_page`. It writes the whole document, from the doctype to `</html>`,
including the header, footer and Rivet's asset tags:

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

The `<title>` is the page's `title`, then `titleSeparator`, then the app title.

## Put content in a slot

Content inside a slot tag moves to that slot's place in the document, wherever you write the tag.
Everything outside the slots is the main content.

| Slot | Where its content goes |
|---|---|
| `rvt_page_styles` | `<head>`, after Rivet's stylesheet |
| `rvt_page_scripts` | Before `</body>`, after Rivet's script |
| `rvt_page_sidebar` | The sidebar |
| `rvt_page_breadcrumbs` | The heading band, or the top of the content under `anchored_sidebar` |

## Choose a layout

Set `layout` on `rvt_page` to one of the `PageLayout` values:

| Layout | Sidebar | Heading and breadcrumbs |
|---|---|---|
| `single_column`, the default | None | Shaded full-width band above the content |
| `sidebar` | Inside the container, no background | Shaded full-width band |
| `anchored_sidebar` | Against the edge of the window | Inside the content, no band |

The heading area appears only when the page sets a `heading` or fills `rvt_page_breadcrumbs`. Filling
`rvt_page_sidebar` under `single_column` throws `InvalidArgumentException`.

## Change the navigation per viewer

To show different header links to different viewers, such as a menu only administrators see, implement
`NavigationProvider`:

```php
use Guild\Rivet\Page\NavigationProvider;
use Guild\Rivet\Page\PageDefaults;

final class AdminAwareNavigation implements NavigationProvider
{
    public function navItems(PageDefaults $defaults): array
    {
        return $this->isAdmin()
            ? [...$defaults->navItems, ['label' => 'Settings', 'href' => '/settings']]
            : $defaults->navItems;
    }
}
```

Pass it to the `Renderer` as `navigation:`. `rvt_page` asks it once each time it renders, so the check
runs only for whole pages. In a Guild app, bind your provider in the container under
`NavigationProvider` instead. The framework's authorization binds its own provider for the System
settings menu, so check the framework's docs before you replace it.

## Related

- [Use components](use-components.md) shows how to write the components inside the page.
- [Components](../reference/components.md) lists the slots and every other component.
