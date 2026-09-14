<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A badge's semantic colour.
 *
 * One of the two independent axes Rivet composes into a single badge modifier; the
 * other is BadgeVariant. They are kept apart rather than flattened into one enum of
 * every class name, so the mapping to CSS lives in one function and a future Rivet
 * release changes that function instead of every call site.
 */
enum BadgeStyle: string
{
    case Base = 'base';
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
