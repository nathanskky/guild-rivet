<?php

declare(strict_types=1);

namespace Guild\Rivet\Exception;

use LogicException;

/**
 * The library was used in a way that needs configuration it was not given.
 *
 * Distinct from InvalidArgumentException, which is about a value that cannot be
 * rendered. This is about something the application was supposed to set up once.
 */
final class ConfigurationException extends LogicException implements RivetException
{
}
