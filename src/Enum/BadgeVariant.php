<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * Whether a badge is filled or outlined.
 *
 * The second axis composed with BadgeStyle into a single Rivet modifier.
 */
enum BadgeVariant: string
{
    case Solid = 'solid';
    case Secondary = 'secondary';
}
