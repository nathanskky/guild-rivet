<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * How a step in a sequence turned out.
 *
 * @see https://rivet.iu.edu/components/step-indicator/
 */
enum StepStatus: string
{
    case Default = 'default';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
