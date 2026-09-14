<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * The HTML type attribute.
 *
 * Always emitted: a button inside a form submits it when the attribute is omitted, which
 * is rarely what the author meant.
 */
enum ButtonType: string
{
    case Button = 'button';
    case Submit = 'submit';
    case Reset = 'reset';
}
