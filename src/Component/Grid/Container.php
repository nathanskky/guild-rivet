<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Grid;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Enum\ContainerSize;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet grid container — constrains and centres page width.
 *
 * @see https://rivet.iu.edu/components/grid/
 */
final class Container extends Component
{
    public function __construct(
        private readonly ContainerSize $size = ContainerSize::Large,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_container';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')->class($this->size->value)->merge($this->extra)->html($content)->render();
    }
}
