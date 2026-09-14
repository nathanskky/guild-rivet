<?php

declare(strict_types=1);

namespace Guild\Rivet\Exception;

use InvalidArgumentException as SplInvalidArgumentException;

/**
 * A component or builder was given a value it cannot render.
 *
 * These are programming errors — a misspelled element, an out-of-range column, a
 * component missing an accessible name — and are meant to surface loudly in development
 * rather than degrade into silently wrong markup.
 */
final class InvalidArgumentException extends SplInvalidArgumentException implements RivetException
{
}
