<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A grid container's maximum width. The value is the whole class, as Rivet spells these
 * as separate classes rather than modifiers.
 *
 * @see https://rivet.iu.edu/components/grid/
 */
enum ContainerSize: string
{
    case Small = 'rvt-container-sm';
    case Medium = 'rvt-container-md';
    case Large = 'rvt-container-lg';
    case ExtraLarge = 'rvt-container-xl';
}
