<?php

declare(strict_types=1);

namespace Guild\Rivet\Html;

use Guild\Rivet\Exception\InvalidArgumentException;

/**
 * Caller-supplied extra attributes for a component.
 *
 * Every component accepts one of these as its escape hatch, so a developer can attach
 * utility classes, data attributes or an id without the library having to anticipate
 * each one.
 *
 * Keys may be written in snake_case and are mapped to hyphens (`data_rvt_dialog`
 * becomes `data-rvt-dialog`). That mapping exists because Twig cannot lex a hyphenated
 * name in tag syntax at all, so snake_case is the only spelling that round-trips
 * identically through both Twig and Latte.
 *
 * `class` is held apart from the other attributes so a component can merge caller
 * classes with its own rather than have them overwritten.
 */
final class Attributes
{
    /**
     * Valid attribute name: a letter, underscore or colon, then name characters.
     * Deliberately stricter than HTML allows — anything outside this is far more
     * likely to be a mistake than an intentional attribute.
     */
    private const string NAME_PATTERN = '/^[a-zA-Z_:][a-zA-Z0-9_:.\-]*$/';

    /** @var array<string, string|true> */
    private array $attributes = [];

    /** @var list<string> */
    private array $classes = [];

    /**
     * @param  array<string, string|bool|int|float|null>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $rawName => $value) {
            $name = str_replace('_', '-', (string) $rawName);

            if (preg_match(self::NAME_PATTERN, $name) !== 1) {
                throw new InvalidArgumentException(
                    sprintf('"%s" is not a valid HTML attribute name.', $rawName),
                );
            }

            if ($name === 'class') {
                foreach (preg_split('/\s+/', trim((string) $value)) ?: [] as $single) {
                    if ($single !== '' && ! in_array($single, $this->classes, true)) {
                        $this->classes[] = $single;
                    }
                }

                continue;
            }

            if ($value === null || $value === false) {
                continue;
            }

            $this->attributes[$name] = $value === true ? true : (string) $value;
        }
    }

    /**
     * Every attribute except `class`.
     *
     * @return array<string, string|true>
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * The caller's class names, in the order given.
     *
     * @return list<string>
     */
    public function classes(): array
    {
        return $this->classes;
    }
}
