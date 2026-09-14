<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * An alert's severity.
 *
 * @see https://rivet.iu.edu/components/alert/
 */
enum AlertStyle: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
