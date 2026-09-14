<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet button group — arranges several buttons, with no grouping semantics.
 *
 * Where the buttons act together as one control, use SegmentedButtons instead, which
 * carries the role and name that relationship needs.
 *
 * @see https://rivet.iu.edu/components/button/
 */
final class ButtonGroup extends Component
{
    private const string BLOCK = 'rvt-button-group';

    public function __construct(
        private readonly bool $alignRight = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_button_group';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(self::BLOCK, $this->alignRight ? self::BLOCK . '--right' : null)
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}
