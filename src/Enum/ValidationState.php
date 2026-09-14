<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * The validation styling on a form control.
 *
 * Independent of whether the surrounding field has error messages: a field with errors
 * implies Danger, but a control can also be marked valid with nothing to say.
 *
 * @see https://rivet.iu.edu/components/text-input/
 */
enum ValidationState: string
{
    case None = 'none';
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
