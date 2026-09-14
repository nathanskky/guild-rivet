<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A table's presentation.
 *
 * Like lists, Rivet spells these as alternative base classes rather than modifiers, so
 * the value here is the whole class.
 *
 * @see https://rivet.iu.edu/components/table/
 */
enum TableStyle: string
{
    case Default = 'rvt-table';
    case Stripes = 'rvt-table-stripes';
    case Plain = 'rvt-table-plain';
    case Cells = 'rvt-table-cells';
    case Compact = 'rvt-table-compact';
}
