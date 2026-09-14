<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Enum\InputType;
use Guild\Rivet\Enum\ValidationState;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet single-line text input.
 *
 * @see https://rivet.iu.edu/components/text-input/
 */
final class TextInput extends FormControl
{
    public function __construct(
        private readonly string $name,
        private readonly InputType $type = InputType::Text,
        private readonly ?string $value = null,
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
        return 'rvt_text_input';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        // An input inside a group takes an extra class. Discovering that from the stack
        // means the author states the relationship once, by nesting, rather than twice.
        $inGroup = $context->closest(InputGroup::class) !== null;

        $input = Html::el('input')
            ->class(
                'rvt-text-input',
                $inGroup ? 'rvt-input-group__input' : null,
                $this->validationClass($context, $this->validation),
            )
            ->attr('type', $this->type->value);

        return $this->applyFieldState($input, $context, $this->id)
            ->attr('name', $this->name)
            ->attr('value', $this->value)
            ->attr('placeholder', $this->placeholder)
            ->attr('readonly', $this->readonly)
            ->attr('disabled', $this->disabled)
            ->merge($this->extra)
            ->render();
    }
}
