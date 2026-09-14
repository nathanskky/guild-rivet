<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet pagination.
 *
 * Every link is labelled individually: "3" on its own is not a useful name for a link.
 * An unavailable arrow is not a disabled link — Rivet removes the anchor entirely and
 * moves the label onto the list item, so there is nothing to tab to.
 *
 * Each item is `['label' => string, 'href' => ?string, 'current' => ?bool]`.
 *
 * @see https://rivet.iu.edu/components/pagination/
 */
final class Pagination extends Component
{
    private const string BLOCK = 'rvt-pagination';

    /**
     * @param  list<array{label?: string, href?: string, current?: bool}>  $items
     */
    public function __construct(
        private readonly array $items = [],
        private readonly ?string $previousHref = null,
        private readonly ?string $nextHref = null,
        private readonly ?string $firstHref = null,
        private readonly ?string $lastHref = null,
        private readonly bool $showArrows = true,
        private readonly bool $showFirstLast = false,
        private readonly string $label = 'More pages of items',
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_pagination';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $children = [];

        if ($this->showFirstLast) {
            $children[] = $this->arrow($this->firstHref, 'chevron-first', 'Go to first page', 'No first page');
        }

        if ($this->showArrows) {
            $children[] = $this->arrow($this->previousHref, 'chevron-left', 'Go to previous page', 'No previous page');
        }

        foreach ($this->items as $item) {
            $children[] = $this->page($item);
        }

        if ($this->showArrows) {
            $children[] = $this->arrow($this->nextHref, 'chevron-right', 'Go to next page', 'No next page');
        }

        if ($this->showFirstLast) {
            $children[] = $this->arrow($this->lastHref, 'chevron-last', 'Go to last page', 'No last page');
        }

        return Html::el('nav')
            ->attr('role', 'navigation')
            ->attr('aria-label', $this->label)
            ->merge($this->extra)
            ->children(Html::el('ul')->class(self::BLOCK)->children(...$children))
            ->render();
    }

    /**
     * @param  array{label?: string, href?: string, current?: bool}  $item
     */
    private function page(array $item): Html
    {
        $label = $item['label'] ?? '';
        $link = Html::el('a')
            ->attr('href', $item['href'] ?? null)
            ->attr('aria-label', 'Page ' . $label)
            ->attr('aria-current', ($item['current'] ?? false) ? 'page' : null)
            ->text($label);

        return Html::el('li')->class(self::BLOCK . '__item')->children($link);
    }

    /**
     * An arrow with nowhere to go loses its anchor and wears the label itself, so it is
     * announced but not focusable.
     */
    private function arrow(?string $href, string $icon, string $available, string $unavailable): Html
    {
        $item = Html::el('li')->class(self::BLOCK . '__item');

        if ($href === null || $href === '') {
            return $item->attr('aria-label', $unavailable)->children(SvgIcon::render($icon));
        }

        return $item->children(
            Html::el('a')->attr('href', $href)->attr('aria-label', $available)->children(SvgIcon::render($icon)),
        );
    }
}
