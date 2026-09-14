<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet side navigation.
 *
 * Described as a tree of data rather than nested components: a navigation menu is the
 * same shape repeated, and an array expresses that far better than four levels of tags.
 *
 * Each item is `['label' => string, 'href' => ?string, 'current' => ?bool,
 * 'children' => ?list<...>]`.
 *
 * @see https://rivet.iu.edu/components/sidenav/
 */
final class Sidenav extends Component
{
    private const string BLOCK = 'rvt-sidenav';

    private const int MAX_DEPTH = 4;

    /**
     * @param  list<array{label?: string, href?: string, current?: bool, children?: array<int, mixed>}>  $items
     */
    public function __construct(
        private readonly string $label,
        private readonly array $items = [],
        private readonly bool $openAll = false,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_sidenav';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->resolveId($context, self::BLOCK, $this->id);

        return Html::el('nav')
            ->class(self::BLOCK)
            ->attr('aria-labelledby', $id . '-label')
            ->attr('data-rvt-sidenav', true)
            ->attr('data-rvt-sidenav-open-all', $this->openAll)
            ->merge($this->extra)
            ->children(
                Html::el('span')->class(self::BLOCK . '__label')->attr('id', $id . '-label')->text($this->label),
                $this->list($this->items, 1, false),
            )
            ->render();
    }

    /**
     * @param  array<int, mixed>  $items
     */
    private function list(array $items, int $depth, bool $nested): Html
    {
        if ($depth > self::MAX_DEPTH) {
            throw new InvalidArgumentException(sprintf(
                'Sidenav supports at most %d levels of nesting.',
                self::MAX_DEPTH,
            ));
        }

        $list = Html::el('ul')
            ->class(self::BLOCK . '__list')
            ->attr('data-rvt-sidenav-list', $nested);

        foreach ($items as $item) {
            $list->children($this->item(is_array($item) ? $item : [], $depth));
        }

        return $list;
    }

    /**
     * Items arrive from templates, so the shape is validated rather than assumed.
     *
     * @param  array<array-key, mixed>  $item
     */
    private function item(array $item, int $depth): Html
    {
        $label = $item['label'] ?? null;

        if (! is_string($label) || $label === '') {
            throw new InvalidArgumentException('Every sidenav item needs a "label".');
        }

        $link = Html::el('a')
            ->class(self::BLOCK . '__link')
            ->attr('href', is_string($item['href'] ?? null) ? $item['href'] : null)
            ->attr('aria-current', ($item['current'] ?? false) === true ? 'page' : null)
            ->text($label);

        $children = $item['children'] ?? null;

        if (! is_array($children) || $children === []) {
            return Html::el('li')->class(self::BLOCK . '__item')->children($link);
        }

        return Html::el('li')
            ->class(self::BLOCK . '__item')
            ->children(
                Html::el('div')
                    ->class(self::BLOCK . '__item-wrapper')
                    ->children($link, $this->toggle($label)),
                $this->list(array_values($children), $depth + 1, true),
            );
    }

    /**
     * Naming the section a toggle opens is more use to a screen reader than "expand".
     */
    private function toggle(string $label): Html
    {
        return Html::el('button')
            ->class(self::BLOCK . '__toggle')
            ->attr('type', 'button')
            ->attr('data-rvt-sidenav-toggle', true)
            ->children(
                Html::el('span')->class('rvt-sr-only')->text('Show more ' . $label . ' links'),
                SvgIcon::render('chevron-down'),
            );
    }
}
