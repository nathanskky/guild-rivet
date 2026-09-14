<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Grid;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet grid column.
 *
 * Widths are scoped to the medium breakpoint and up, because Rivet columns stack on
 * small screens. A column with no width shares the row equally with its siblings.
 * Push and pull shift a column visually without moving it in source order.
 *
 * @see https://rivet.iu.edu/components/grid/
 */
final class Column extends Component
{
    private const string BLOCK = 'rvt-cols';

    private const int COLUMNS = 12;

    public function __construct(
        private readonly ?int $width = null,
        private readonly ?int $push = null,
        private readonly ?int $pull = null,
        private readonly bool $last = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_column';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(
                $this->width === null
                    ? self::BLOCK
                    : self::BLOCK . '-' . $this->span('width', $this->width) . '-md',
                $this->push === null ? null : self::BLOCK . '-push-' . $this->span('push', $this->push) . '-md',
                $this->pull === null ? null : self::BLOCK . '-pull-' . $this->span('pull', $this->pull) . '-md',
                $this->last ? self::BLOCK . '--last' : null,
            )
            ->merge($this->extra)
            ->html($content)
            ->render();
    }

    private function span(string $what, int $value): int
    {
        if ($value < 1 || $value > self::COLUMNS) {
            throw new InvalidArgumentException(sprintf(
                'Column %s must be between 1 and %d, %d given.',
                $what,
                self::COLUMNS,
                $value,
            ));
        }

        return $value;
    }
}
