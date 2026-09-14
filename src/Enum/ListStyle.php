<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A list's presentation.
 *
 * Unusually for Rivet these are separate base classes rather than modifiers on one
 * block — `rvt-list-plain`, not `rvt-list--plain` — so the value here is the whole class.
 *
 * @see https://rivet.iu.edu/components/list/
 */
enum ListStyle: string
{
    case Default = 'rvt-list';
    case Plain = 'rvt-list-plain';
    case Inline = 'rvt-list-inline';
    case Description = 'rvt-list-description';
}
