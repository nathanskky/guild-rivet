<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet subnav — navigation within a section, outside the header or sidenav.
 *
 * Each item is `['label' => string, 'href' => ?string, 'current' => ?bool]`.
 *
 * @see https://rivet.iu.edu/components/subnav/
 */
final class Subnav extends Component
{
    private const string BLOCK = 'rvt-subnav';

    /**
     * @param  list<array{label?: string, href?: string, current?: bool}>  $items
     */
    public function __construct(
        private readonly array $items = [],
        private readonly string $label = 'Section navigation',
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_subnav';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $list = Html::el('ul')->class(self::BLOCK . '__list');

        foreach ($this->items as $position => $item) {
            $label = $item['label'] ?? throw new InvalidArgumentException(
                sprintf('Subnav item %d needs a "label".', $position + 1),
            );

            $list->children(
                Html::el('li')->class(self::BLOCK . '__item')->children(
                    Html::el('a')
                        ->attr('href', $item['href'] ?? null)
                        ->attr('aria-current', ($item['current'] ?? false) ? 'page' : null)
                        ->text($label),
                ),
            );
        }

        return Html::el('nav')
            ->class(self::BLOCK)
            ->attr('aria-label', $this->label)
            ->merge($this->extra)
            ->children($list)
            ->render();
    }
}
