<?php

declare(strict_types=1);

namespace Guild\Rivet\Exception;

use LogicException;

/**
 * A component was used outside the parent it depends on.
 *
 * Raised when, say, a dialog close button cannot find the dialog whose id it needs to
 * reference. Both engines also catch this class of mistake at compile time, where the
 * error carries a file and line; this runtime check backs that up for components
 * constructed directly in PHP.
 */
final class ComponentContextException extends LogicException implements RivetException
{
}
