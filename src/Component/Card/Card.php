<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Card;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet card.
 *
 * Rivet's docs wrap groups of utility classes in `[ ]`. The brackets are CUBE-CSS
 * grouping tokens that appear in Rivet's own rendered HTML, not a documentation-only
 * device — but they match no selector, so they are inert, and this library's class
 * deduplication and ordering could not reproduce them even if it tried. Add utilities
 * such as `rvt-flow` through the usual `class` attribute, without the brackets.
 *
 * @see https://rivet.iu.edu/components/card/
 */
final class Card extends Component
{
    private const string BLOCK = 'rvt-card';

    public function __construct(
        private readonly bool $raised = false,
        private readonly bool $horizontal = false,
        private readonly bool $clickable = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_card';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    /**
     * Whether the whole card is a click target.
     *
     * Read by the card body, which must render its title as a link for this to work.
     */
    public function isClickable(): bool
    {
        return $this->clickable;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(
                self::BLOCK,
                $this->horizontal ? self::BLOCK . '--horizontal' : null,
                $this->raised ? self::BLOCK . '--raised' : null,
                $this->clickable ? self::BLOCK . '--clickable' : null,
            )
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}
