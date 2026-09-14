<?php

declare(strict_types=1);

namespace Guild\Rivet\Html;

/**
 * Composes Rivet's two-axis modifier classes.
 *
 * Several Rivet components crossbreed a semantic tone with a filled/outlined variant into
 * one class: `rvt-badge--danger-secondary`, `rvt-button--success-secondary`. Holding that
 * rule in one place is what keeps each component's enums independent, so a future Rivet
 * release that drops or renames tones changes this function rather than every call site.
 */
final class Modifier
{
    /**
     * @param  string  $block  the base class, such as `rvt-button`
     * @param  string|null  $tone  the semantic tone, or null for the default tone
     * @param  bool  $secondary  whether the outlined form is wanted
     * @return string|null the modifier class, or null when the unmodified block is correct
     */
    public static function compose(string $block, ?string $tone, bool $secondary): ?string
    {
        if ($tone === null) {
            return $secondary ? $block . '--secondary' : null;
        }

        return $block . '--' . $tone . ($secondary ? '-secondary' : '');
    }
}
