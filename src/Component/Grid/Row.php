<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Grid;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Enum\RowSpacing;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet grid row — holds columns and sets the gutter between them.
 *
 * @see https://rivet.iu.edu/components/grid/
 */
final class Row extends Component
{
    private const string BLOCK = 'rvt-row';

    public function __construct(
        private readonly RowSpacing $spacing = RowSpacing::Default,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_row';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(
                self::BLOCK,
                $this->spacing === RowSpacing::Default ? null : self::BLOCK . '--' . $this->spacing->value,
            )
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}
