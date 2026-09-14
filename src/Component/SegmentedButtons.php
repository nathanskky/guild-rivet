<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet segmented buttons — several buttons acting as one control.
 *
 * The label is required: `role="group"` without an accessible name tells assistive
 * technology that a relationship exists while withholding what it is.
 *
 * @see https://rivet.iu.edu/components/button/
 */
final class SegmentedButtons extends Component
{
    private const string BLOCK = 'rvt-button-segmented';

    public function __construct(
        private readonly string $label,
        private readonly bool $fitted = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_segmented_buttons';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        if ($this->label === '') {
            throw new InvalidArgumentException(self::name() . ' requires a label naming the group.');
        }

        return Html::el('div')
            ->class(self::BLOCK, $this->fitted ? self::BLOCK . '--fitted' : null)
            ->attr('role', 'group')
            ->attr('aria-label', $this->label)
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}
