<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Enum\ContainerSize;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet application header.
 *
 * Structurally the largest thing Rivet documents, and described here as data rather than
 * nested tags: a navigation menu is one shape repeated, and an array expresses that far
 * better than four levels of markup. Each item is `['label' => string, 'href' => ?string,
 * 'current' => ?bool, 'children' => ?list<...>]`; an item with children becomes a
 * dropdown.
 *
 * Rules Rivet states and this component keeps: the skip link is the first thing inside
 * the header and points at the page's main content, the primary nav is labelled "Main",
 * and the container width should match the page's. Rivet's documented markup hard-codes
 * `id="search"`, which collides with anything else on the page using that id, so every
 * identifier here derives from the header's own.
 *
 * The header has no JavaScript of its own — it is built from a disclosure and dropdowns,
 * which Rivet's script drives.
 *
 * @see https://rivet.iu.edu/components/header/
 */
final class Header extends Component
{
    private const string BLOCK = 'rvt-header';

    private const string MENU = 'rvt-header-menu';

    /**
     * Items are typed loosely because they usually arrive from a template, where nothing
     * guarantees their shape; each is validated as it is read. The expected form is
     * `['label' => string, 'href' => ?string, 'current' => ?bool, 'children' => ?list]`.
     *
     * @param  list<mixed>  $items
     */
    public function __construct(
        private readonly string $title,
        private readonly ?string $subtitle = null,
        private readonly string $href = '/',
        private readonly array $items = [],
        private readonly ContainerSize $containerSize = ContainerSize::ExtraLarge,
        private readonly string $skipLinkHref = '#main-content',
        private readonly string $skipLinkText = 'Skip to main content',
        private readonly string $navLabel = 'Main',
        private readonly ?string $searchAction = null,
        private readonly string $searchLabel = 'Search',
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_header';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->resolveId($context, self::BLOCK, $this->id);

        return Html::el('header')
            ->class(self::BLOCK . '-wrapper')
            ->merge($this->extra)
            ->children(
                // Rivet fixes this as the first element: a keyboard user must reach it
                // before the navigation they are trying to skip.
                Html::el('a')
                    ->class(self::BLOCK . '-wrapper__skip-link')
                    ->attr('href', $this->skipLinkHref)
                    ->text($this->skipLinkText),
                Html::el('div')->class(self::BLOCK . '-global')->children(
                    Html::el('div')->class($this->containerSize->value)->children(
                        Html::el('div')->class(self::BLOCK . '-global__inner')->children(
                            Html::el('div')->class(self::BLOCK . '-global__logo-slot')->children($this->lockup()),
                            Html::el('div')->class(self::BLOCK . '-global__controls')->children(
                                $this->navigation($id),
                                $this->search($id),
                            ),
                        ),
                    ),
                ),
            )
            ->render();
    }

    private function lockup(): Html
    {
        $body = Html::el('div')
            ->class('rvt-lockup__body')
            ->children(Html::el('span')->class('rvt-lockup__title')->text($this->title));

        if ($this->subtitle !== null && $this->subtitle !== '') {
            $body->children(Html::el('span')->class('rvt-lockup__subtitle')->text($this->subtitle));
        }

        return Html::el('a')
            ->class('rvt-lockup')
            ->attr('href', $this->href)
            ->attr('aria-label', $this->title . ' home')
            ->children(
                Html::el('div')->class('rvt-lockup__tab')->children(SvgIcon::lockupTrident()),
                $body,
            );
    }

    private function navigation(string $id): Html
    {
        $menuId = $id . '-menu';
        $list = Html::el('ul')->class(self::MENU . '__list');

        foreach ($this->items as $position => $item) {
            $list->children($this->item(is_array($item) ? $item : [], $id, $position));
        }

        return Html::el('div')
            ->attr('data-rvt-disclosure', $menuId)
            ->attr('data-rvt-close-click-outside', true)
            ->children(
                Html::el('button')
                    ->class('rvt-global-toggle', 'rvt-global-toggle--menu', 'rvt-hide-lg-up')
                    ->attr('type', 'button')
                    ->attr('aria-expanded', 'false')
                    ->attr('data-rvt-disclosure-toggle', $menuId)
                    ->children(
                        Html::el('span')->class('rvt-sr-only')->text('Menu'),
                        SvgIcon::render('menu'),
                    ),
                Html::el('nav')
                    ->class(self::MENU)
                    ->attr('aria-label', $this->navLabel)
                    ->attr('data-rvt-disclosure-target', $menuId)
                    ->attr('hidden', true)
                    ->children($list),
            );
    }

    /**
     * @param  array<array-key, mixed>  $item
     */
    private function item(array $item, string $id, int $position): Html
    {
        $label = $item['label'] ?? null;

        if (! is_string($label) || $label === '') {
            throw new InvalidArgumentException(sprintf('Header item %d needs a "label".', $position + 1));
        }

        $isCurrent = ($item['current'] ?? false) === true;
        $href = is_string($item['href'] ?? null) ? $item['href'] : null;

        $link = Html::el('a')
            ->class(self::MENU . '__link')
            ->attr('href', $href)
            ->attr('aria-current', $isCurrent ? 'page' : null)
            ->text($label);

        $listItem = Html::el('li')
            ->class(self::MENU . '__item', $isCurrent ? self::MENU . '__item--current' : null);

        $children = $item['children'] ?? null;

        if (! is_array($children) || $children === []) {
            return $listItem->children($link);
        }

        return $listItem->children($this->dropdown($link, array_values($children), $label, $id, $position));
    }

    /**
     * @param  list<mixed>  $children
     */
    private function dropdown(Html $link, array $children, string $label, string $id, int $position): Html
    {
        $dropdownId = $id . '-nav-' . ($position + 1);
        $submenu = Html::el('ul')->class(self::MENU . '__submenu-list');

        foreach ($children as $child) {
            $child = is_array($child) ? $child : [];
            $childLabel = $child['label'] ?? '';

            $submenu->children(
                Html::el('li')->class(self::MENU . '__submenu-item')->children(
                    Html::el('a')
                        ->class(self::MENU . '__submenu-link')
                        ->attr('href', is_string($child['href'] ?? null) ? $child['href'] : null)
                        ->text(is_string($childLabel) ? $childLabel : ''),
                ),
            );
        }

        return Html::el('div')
            ->class(self::MENU . '__dropdown', 'rvt-dropdown')
            ->attr('data-rvt-dropdown', $dropdownId)
            ->children(
                Html::el('div')->class(self::MENU . '__group')->children(
                    $link,
                    Html::el('button')
                        ->class('rvt-dropdown__toggle', self::MENU . '__toggle')
                        ->attr('type', 'button')
                        ->attr('aria-expanded', 'false')
                        ->attr('data-rvt-dropdown-toggle', $dropdownId)
                        ->children(
                            Html::el('span')->class('rvt-sr-only')->text('More ' . $label . ' links'),
                            SvgIcon::render('chevron-down'),
                        ),
                ),
                Html::el('div')
                    ->class(self::MENU . '__submenu', 'rvt-dropdown__menu', 'rvt-dropdown__menu--right')
                    ->attr('data-rvt-dropdown-menu', $dropdownId)
                    ->attr('hidden', true)
                    ->children($submenu),
            );
    }

    private function search(string $id): ?Html
    {
        if ($this->searchAction === null || $this->searchAction === '') {
            return null;
        }

        $searchId = $id . '-search';
        $disclosureId = $id . '-search-disclosure';

        return Html::el('div')
            ->attr('data-rvt-disclosure', $disclosureId)
            ->attr('data-rvt-close-click-outside', true)
            ->children(
                Html::el('button')
                    ->class('rvt-global-toggle')
                    ->attr('type', 'button')
                    ->attr('aria-expanded', 'false')
                    ->attr('data-rvt-disclosure-toggle', $disclosureId)
                    ->children(
                        Html::el('span')->class('rvt-sr-only')->text($this->searchLabel),
                        SvgIcon::render('search'),
                    ),
                Html::el('form')
                    ->class(self::BLOCK . '-global__search')
                    ->attr('action', $this->searchAction)
                    ->attr('method', 'get')
                    ->attr('role', 'search')
                    ->attr('data-rvt-disclosure-target', $disclosureId)
                    ->attr('hidden', true)
                    ->children(
                        Html::el('label')->class('rvt-sr-only')->attr('for', $searchId)->text($this->searchLabel),
                        Html::el('div')->class('rvt-input-group')->children(
                            Html::el('input')
                                ->class('rvt-input-group__input', 'rvt-text-input')
                                ->attr('id', $searchId)
                                ->attr('type', 'text')
                                ->attr('name', 'q'),
                            Html::el('div')->class('rvt-input-group__append')->children(
                                Html::el('button')->class('rvt-button')->attr('type', 'submit')->text($this->searchLabel),
                            ),
                        ),
                    ),
            );
    }
}
