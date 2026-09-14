<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * The HTML type of a text-like input.
 *
 * Restricted to the types Rivet styles as a text input; a checkbox, radio or file input
 * is a separate component because its markup differs.
 */
enum InputType: string
{
    case Text = 'text';
    case Email = 'email';
    case Password = 'password';
    case Search = 'search';
    case Tel = 'tel';
    case Url = 'url';
    case Number = 'number';
    case Date = 'date';
    case Time = 'time';
}
