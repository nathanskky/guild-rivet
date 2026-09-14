<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet input group — an input with something attached to one or both ends.
 *
 * The label stays outside the group, on the enclosing FormField, so the group holds only
 * the input and its addons.
 *
 * @see https://rivet.iu.edu/components/input-group/
 */
final class InputGroup extends Component
{
    public function __construct(
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_input_group';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class('rvt-input-group')
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}
