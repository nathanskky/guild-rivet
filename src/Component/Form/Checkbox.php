<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

/**
 * Rivet checkbox.
 *
 * Valid on its own — a single "I agree" box needs no group — as well as inside a
 * FieldGroup alongside related options.
 *
 * @see https://rivet.iu.edu/components/checkbox/
 */
final class Checkbox extends Choice
{
    public static function name(): string
    {
        return 'rvt_checkbox';
    }

    protected static function block(): string
    {
        return 'rvt-checkbox';
    }

    protected static function inputType(): string
    {
        return 'checkbox';
    }

    protected static function requiresGroup(): bool
    {
        return false;
    }
}
