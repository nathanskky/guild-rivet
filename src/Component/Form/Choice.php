<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Shared rendering for checkboxes and radios, which differ only in their block class,
 * their input type, and whether a surrounding group is mandatory.
 *
 * Unlike a text input, a choice owns its label: the group's legend states the question,
 * each choice states one answer. Ids are generated per choice, since choices are nearly
 * always rendered in a loop — exactly where hand-written ids collide.
 */
abstract class Choice extends Component
{
    public function __construct(
        protected readonly string $name,
        protected readonly string $value,
        protected readonly string $label,
        protected readonly bool $checked = false,
        protected readonly bool $disabled = false,
        protected readonly ?string $description = null,
        protected readonly bool $labelHidden = false,
        protected readonly ?string $id = null,
        protected readonly Attributes $extra = new Attributes(),
    ) {
    }

    /**
     * The Rivet block class, `rvt-checkbox` or `rvt-radio`.
     */
    abstract protected static function block(): string;

    /**
     * The input's type attribute.
     */
    abstract protected static function inputType(): string;

    /**
     * Whether Rivet requires this choice to sit inside a fieldset.
     */
    abstract protected static function requiresGroup(): bool;

    public function render(RenderContext $context, string $content = ''): string
    {
        $group = static::requiresGroup()
            ? $context->requireAncestor(FieldGroup::class, static::class)
            : $context->closest(FieldGroup::class);

        $id = $this->resolveId($context, static::block(), $this->id);
        $descriptionId = $this->description === null ? null : $id . '-description';

        $describedBy = implode(' ', array_filter([
            $descriptionId,
            $group?->describedBy($context),
        ]));

        $choice = Html::el('div')
            ->class(static::block(), $this->labelHidden ? static::block() . '--sr-only-label' : null)
            ->merge($this->extra)
            ->children(
                Html::el('input')
                    ->attr('type', static::inputType())
                    ->attr('id', $id)
                    ->attr('name', $this->name)
                    ->attr('value', $this->value)
                    ->attr('checked', $this->checked)
                    ->attr('disabled', $this->disabled)
                    ->attr('aria-describedby', $describedBy === '' ? null : $describedBy)
                    ->attr('aria-invalid', $group?->hasErrors() === true ? 'true' : null),
                Html::el('label')->attr('for', $id)->text($this->label),
                $descriptionId === null
                    ? null
                    : Html::el('div')
                        ->class(static::block() . '__description')
                        ->attr('id', $descriptionId)
                        ->text($this->description ?? ''),
            );

        // Inside a group the choices are list items; on its own a choice stands alone.
        return $group === null
            ? $choice->render()
            : Html::el('li')->children($choice)->render();
    }
}
