<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Enum\ValidationState;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet multi-line text input.
 *
 * @see https://rivet.iu.edu/components/textarea/
 */
final class Textarea extends FormControl
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $value = null,
        private readonly ?int $rows = null,
        private readonly ?string $placeholder = null,
        private readonly bool $readonly = false,
        private readonly bool $disabled = false,
        private readonly ValidationState $validation = ValidationState::None,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_textarea';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $textarea = Html::el('textarea')
            ->class('rvt-textarea', $this->validationClass($context, $this->validation));

        return $this->applyFieldState($textarea, $context, $this->id)
            ->attr('name', $this->name)
            ->attr('rows', $this->rows === null ? null : (string) $this->rows)
            ->attr('placeholder', $this->placeholder)
            ->attr('readonly', $this->readonly)
            ->attr('disabled', $this->disabled)
            ->merge($this->extra)
            ->text($this->value ?? '')
            ->render();
    }
}
