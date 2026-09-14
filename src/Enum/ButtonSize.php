<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A button's size.
 *
 * @see https://rivet.iu.edu/components/button/
 */
enum ButtonSize: string
{
    case Default = 'default';
    case Small = 'small';
}
