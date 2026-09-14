<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\LoaderSize;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet loading indicator.
 *
 * Always carries an accessible name: it conveys state but contains no text, so without
 * one a screen reader announces nothing at all.
 *
 * @see https://rivet.iu.edu/components/loading-indicator/
 */
final class LoadingIndicator extends Component
{
    private const string BLOCK = 'rvt-loader';

    public function __construct(
        private readonly LoaderSize $size = LoaderSize::Default,
        private readonly string $label = 'Content loading',
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_loader';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(
                self::BLOCK,
                $this->size === LoaderSize::Default ? null : self::BLOCK . '--' . $this->size->value,
            )
            ->attr('aria-label', $this->label)
            ->merge($this->extra)
            ->render();
    }
}
