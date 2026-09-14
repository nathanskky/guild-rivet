<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * Which side of an input an addon sits on.
 *
 * @see https://rivet.iu.edu/components/input-group/
 */
enum AddonPosition: string
{
    case Prepend = 'prepend';
    case Append = 'append';
}
