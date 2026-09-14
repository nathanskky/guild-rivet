<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * Whether a button is filled or outlined.
 *
 * Rivet spells the outlined form `--secondary`, either alone or hyphenated onto a
 * purpose, as in `rvt-button--danger-secondary`.
 *
 * @see https://rivet.iu.edu/components/button/
 */
enum ButtonFill: string
{
    case Solid = 'solid';
    case Outline = 'outline';
}
