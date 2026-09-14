<?php

declare(strict_types=1);

namespace Guild\Rivet\Exception;

use Throwable;

/**
 * Implemented by every exception this package throws.
 *
 * Concrete exceptions extend the SPL type that matches their meaning, so they keep
 * standard semantics, while this interface lets a consumer catch anything originating
 * from Rivet with a single catch block.
 */
interface RivetException extends Throwable
{
}
