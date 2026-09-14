<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Internal;

use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Html;

/**
 * The fixed set of icons this library embeds inside its own components.
 *
 * Distinct from the public Icon component, which emits `<rvt-icon>` for icons a
 * developer chooses. Rivet documents `<rvt-icon>` as the preferred form, but it is
 * served by a separate `rivet-icons` package. Component chrome — a dialog's close
 * button, an alert's dismiss control — embeds its SVG directly so a component keeps
 * working for an application that loaded only Rivet's core CSS and JS. An invisible
 * close button is a functional break, not a cosmetic one.
 *
 * Path data is copied verbatim from Rivet's own component documentation.
 *
 * @internal
 */
final class SvgIcon
{
    /** @var array<string, string> */
    private const array PATHS = [
        'close' => 'm3.5 2.086 4.5 4.5 4.5-4.5L13.914 3.5 9.414 8l4.5 4.5-1.414 1.414-4.5-4.5-4.5 4.5L2.086 12.5l4.5-4.5-4.5-4.5L3.5 2.086Z',
    ];

    /**
     * Render one icon, marked aria-hidden because the control around it carries the name.
     */
    public static function render(string $name): Html
    {
        if (! array_key_exists($name, self::PATHS)) {
            throw new InvalidArgumentException(sprintf('Unknown built-in icon "%s".', $name));
        }

        return Html::el('svg')
            ->attr('xmlns', 'http://www.w3.org/2000/svg')
            ->attr('width', '16')
            ->attr('height', '16')
            ->attr('fill', 'currentColor')
            ->attr('viewBox', '0 0 16 16')
            ->attr('aria-hidden', 'true')
            ->html('<path d="' . Html::escape(self::PATHS[$name]) . '"/>');
    }
}
