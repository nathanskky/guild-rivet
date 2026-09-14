<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

/**
 * Rivet radio input.
 *
 * Must sit inside a FieldGroup. A radio is meaningless alone — it exists to offer one of
 * several answers — and Rivet documents the fieldset and legend as required here, unlike
 * for checkboxes where the legend may merely be hidden.
 *
 * @see https://rivet.iu.edu/components/radio-input/
 */
final class Radio extends Choice
{
    public static function name(): string
    {
        return 'rvt_radio';
    }

    protected static function block(): string
    {
        return 'rvt-radio';
    }

    protected static function inputType(): string
    {
        return 'radio';
    }

    protected static function requiresGroup(): bool
    {
        return true;
    }
}
