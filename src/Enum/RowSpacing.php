<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * The gutter between columns in a row.
 *
 * @see https://rivet.iu.edu/components/grid/
 */
enum RowSpacing: string
{
    case Default = 'default';
    case Loose = 'loose';
    case Tight = 'tight';
}
