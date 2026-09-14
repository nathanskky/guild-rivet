<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Enum\ValidationState;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet select input.
 *
 * Options are ordinary data, so they are described as an array rather than a component
 * per option. Each is `['value' => string, 'label' => string, 'selected' => ?bool,
 * 'disabled' => ?bool]`.
 *
 * @see https://rivet.iu.edu/components/select-input/
 */
final class Select extends FormControl
{
    /**
     * @param  list<array{value?: string, label?: string, selected?: bool, disabled?: bool}>  $options
     */
    public function __construct(
        private readonly string $name,
        private readonly array $options = [],
        private readonly ?string $placeholder = null,
        private readonly bool $disabled = false,
        private readonly ValidationState $validation = ValidationState::None,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_select';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $select = Html::el('select')
            ->class('rvt-select', $this->validationClass($context, $this->validation));

        return $this->applyFieldState($select, $context, $this->id)
            ->attr('name', $this->name)
            ->attr('disabled', $this->disabled)
            ->merge($this->extra)
            ->children(...$this->optionElements())
            ->render();
    }

    /**
     * @return list<Html>
     */
    private function optionElements(): array
    {
        $elements = [];

        // A prompt is a disabled, selected, empty option: visible as the initial choice
        // but not selectable, so it cannot be submitted as a real answer.
        if ($this->placeholder !== null && $this->placeholder !== '') {
            $elements[] = Html::el('option')
                ->attr('value', '')
                ->attr('disabled', true)
                ->attr('selected', true)
                ->text($this->placeholder);
        }

        foreach ($this->options as $option) {
            $elements[] = Html::el('option')
                ->attr('value', $option['value'] ?? '')
                ->attr('selected', $option['selected'] ?? false)
                ->attr('disabled', $option['disabled'] ?? false)
                ->text($option['label'] ?? '');
        }

        return $elements;
    }
}
