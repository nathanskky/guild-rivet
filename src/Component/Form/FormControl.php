<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Enum\ValidationState;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Shared behaviour for controls that live inside a FormField.
 *
 * Every such control must be written inside a field, which is how the library enforces
 * Rivet's rule that a control always has a label bound to it by `for`/`id`. The field
 * supplies the id, the describedby value and the required flag, so no control has to be
 * told twice and the two can never disagree.
 *
 * Split into two steps so each control keeps Rivet's attribute order: the class and any
 * element-specific attributes are applied first, then the field's state.
 */
abstract class FormControl extends Component
{
    protected function field(RenderContext $context): FormField
    {
        return $context->requireAncestor(FormField::class, static::class);
    }

    /**
     * The Rivet validation class for this control, or null for none.
     *
     * An explicit state wins; otherwise a field carrying errors styles its control as
     * invalid, which is the wiring authors most often forget by hand.
     */
    protected function validationClass(RenderContext $context, ValidationState $explicit): ?string
    {
        $state = $explicit !== ValidationState::None
            ? $explicit
            : ($this->field($context)->hasErrors() ? ValidationState::Danger : ValidationState::None);

        return $state === ValidationState::None ? null : 'rvt-validation-' . $state->value;
    }

    /**
     * Apply the id and ARIA state the enclosing field decides.
     */
    protected function applyFieldState(Html $control, RenderContext $context, ?string $id): Html
    {
        $field = $this->field($context);

        return $control
            ->attr('id', $id ?? $field->fieldId($context))
            ->attr('aria-describedby', $field->describedBy($context))
            ->attr('required', $field->isRequired())
            ->attr('aria-required', $field->isRequired() ? 'true' : null)
            ->attr('aria-invalid', $field->hasErrors() ? 'true' : null);
    }
}
