<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * Where a dialog sits on the page.
 *
 * @see https://rivet.iu.edu/components/dialog/
 */
enum DialogPosition: string
{
    case Default = 'default';
    case TopLeft = 'top-left';
    case TopRight = 'top-right';
    case BottomLeft = 'bottom-left';
    case BottomRight = 'bottom-right';
}
