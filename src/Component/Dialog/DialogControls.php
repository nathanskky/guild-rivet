<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Dialog;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * The row of buttons at the foot of a dialog.
 *
 * @see https://rivet.iu.edu/components/dialog/
 */
final class DialogControls extends Component
{
    public function __construct(
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_dialog_controls';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $context->requireAncestor(Dialog::class, self::class);

        return Html::el('div')
            ->class('rvt-dialog__controls')
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}
