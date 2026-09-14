<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * A dialog's purpose, which sets defaults for its behaviour and position.
 *
 * Each behaviour can still be stated explicitly; the variant only decides what happens
 * when it is not.
 *
 * @see https://rivet.iu.edu/components/dialog/
 */
enum DialogVariant: string
{
    case Default = 'default';
    case Modal = 'modal';
    case Confirmation = 'confirmation';
    case Notification = 'notification';
    case HelpWidget = 'help_widget';
}
