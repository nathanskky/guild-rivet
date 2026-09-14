<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A button's semantic purpose.
 *
 * One of two axes Rivet composes into a single modifier; the other is ButtonFill.
 *
 * @see https://rivet.iu.edu/components/button/
 */
enum ButtonPurpose: string
{
    case Default = 'default';
    case Success = 'success';
    case Danger = 'danger';
    case Plain = 'plain';
}
