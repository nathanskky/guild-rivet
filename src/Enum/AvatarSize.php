<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * An avatar's size.
 *
 * @see https://rivet.iu.edu/components/avatar/
 */
enum AvatarSize: string
{
    case Default = 'default';
    case ExtraSmall = 'xs';
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';
}
