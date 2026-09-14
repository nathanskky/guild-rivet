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
    /**
     * Icon name to its path data. Several icons are two paths — a glyph and the circle
     * around it — so every entry is a list.
     *
     * @var array<string, list<string>>
     */
    private const array PATHS = [
        'close' => ['m3.5 2.086 4.5 4.5 4.5-4.5L13.914 3.5 9.414 8l4.5 4.5-1.414 1.414-4.5-4.5-4.5 4.5L2.086 12.5l4.5-4.5-4.5-4.5L3.5 2.086Z'],
        'file' => ['M2 1h8.414L14 4.586V15H2V1Zm2 2v10h8V7.5H7.5V3H4Zm5.5 0v2.5H12v-.086L9.586 3H9.5Z'],
        'home' => ['m8 .798 7 4.667V15H9v-4.444H7V15H1V5.465L8 .798ZM3 6.535V13h2V8.556h6V13h2V6.535L8 3.202 3 6.535Z'],
        'alert-info' => [
            'M9 7v5H7V7h2ZM8 4a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z',
            'M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8Zm8-6a6 6 0 1 0 0 12A6 6 0 0 0 8 2Z',
        ],
        'alert-success' => [
            'M7 11.414 11.914 6.5 10.5 5.086 7 8.586l-1.5-1.5L4.086 8.5 7 11.414Z',
            'M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0ZM2 8a6 6 0 1 1 12 0A6 6 0 0 1 2 8Z',
        ],
        'alert-warning' => [
            'M12 7H4v2h8V7Z',
            'M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0ZM2 8a6 6 0 1 1 12 0A6 6 0 0 1 2 8Z',
        ],
        'alert-danger' => [
            'm8 6.586-2-2L4.586 6l2 2-2 2L6 11.414l2-2 2 2L11.414 10l-2-2 2-2L10 4.586l-2 2Z',
            'M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0ZM2 8a6 6 0 1 1 12 0A6 6 0 0 1 2 8Z',
        ],
        'chevron-left' => ['M9.737.854 3.69 8l6.047 7.146 1.526-1.292L6.31 8l4.953-5.854L9.737.854Z'],
        'chevron-right' => ['M6.263 15.146 12.31 8 6.263.854 4.737 2.146 9.69 8l-4.953 5.854 1.526 1.292Z'],
        'chevron-first' => [
            'M.586 8 7 14.414 8.414 13l-5-5 5-5L7 1.586.586 8Z',
            'M6.586 8 13 14.414 14.414 13l-5-5 5-5L13 1.586 6.586 8Z',
        ],
        'chevron-last' => [
            'M9.414 8 3 1.586 1.586 3l5 5-5 5L3 14.414 9.414 8Z',
            'M15.414 8 9 1.586 7.586 3l5 5-5 5L9 14.414 15.414 8Z',
        ],
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
            ->html(implode('', array_map(
                static fn (string $path): string => '<path d="' . Html::escape($path) . '"/>',
                self::PATHS[$name],
            )));
    }
}
