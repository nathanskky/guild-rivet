<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A loading indicator's size.
 *
 * @see https://rivet.iu.edu/components/loading-indicator/
 */
enum LoaderSize: string
{
    case Default = 'default';
    case ExtraExtraSmall = 'xxs';
    case ExtraSmall = 'xs';
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';
    case ExtraExtraLarge = 'xxl';
}
