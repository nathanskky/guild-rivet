<?php

declare(strict_types=1);

namespace Guild\Rivet\Html;

use Guild\Rivet\Exception\InvalidArgumentException;

/**
 * A minimal HTML element builder.
 *
 * Escaping is a visible choice in the method name — text() escapes, html() does not —
 * so an unescaped value is always deliberate rather than an omission.
 */
final class Html
{
    /**
     * Elements that are self-closing in HTML and must never be given content.
     *
     * @var list<string>
     */
    private const array VOID_ELEMENTS = [
        'area', 'base', 'br', 'col', 'embed', 'hr',
        'img', 'input', 'link', 'meta', 'source', 'track', 'wbr',
    ];

    /** @var array<string, string|true> Attributes in insertion order; true means a bare boolean attribute. */
    private array $attributes = [];

    /** @var list<string> Distinct class names in insertion order. */
    private array $classes = [];

    /** @var list<string> Rendered child fragments, already escaped where appropriate. */
    private array $children = [];

    private function __construct(
        private readonly string $tag,
    ) {
    }

    public static function el(string $tag): self
    {
        return new self($tag);
    }

    /**
     * Set an attribute.
     *
     * null and false drop the attribute entirely, so callers can pass a conditional
     * expression inline. true renders the name with no value, which is the form
     * Rivet's data-rvt-* behaviour flags take.
     */
    public function attr(string $name, string|bool|null $value): self
    {
        if ($value === null || $value === false) {
            unset($this->attributes[$name]);

            return $this;
        }

        $this->attributes[$name] = $value === true ? true : $value;

        return $this;
    }

    /**
     * Add one or more class names.
     *
     * Accumulates across calls, ignores null and empty values, and collapses duplicates
     * while preserving first-seen order. A value may itself contain several
     * space-separated names. The attribute keeps the position of the first call that
     * contributed a name, so adding a modifier later never reorders the output.
     */
    public function class(?string ...$names): self
    {
        foreach ($names as $name) {
            if ($name === null) {
                continue;
            }

            foreach (preg_split('/\\s+/', trim($name)) ?: [] as $single) {
                if ($single !== '' && ! in_array($single, $this->classes, true)) {
                    $this->classes[] = $single;
                }
            }
        }

        if ($this->classes !== []) {
            // Re-assigning an existing key preserves its original position in the map.
            $this->attributes['class'] = implode(' ', $this->classes);
        }

        return $this;
    }

    /**
     * Merge caller-supplied extra attributes into this element.
     *
     * Caller classes are appended after the component's own. Other attributes are added
     * only where the component has not already set them — the component wins on
     * conflict, so a caller cannot accidentally break ARIA wiring the component is
     * responsible for.
     */
    public function merge(Attributes $extra): self
    {
        $this->class(...$extra->classes());

        foreach ($extra->all() as $name => $value) {
            if (array_key_exists($name, $this->attributes)) {
                continue;
            }

            $this->attributes[$name] = $value;
        }

        return $this;
    }

    /**
     * Append text content, HTML-escaped.
     */
    public function text(string $value): self
    {
        $this->guardAgainstContent();
        $this->children[] = self::escape($value);

        return $this;
    }

    /**
     * Append already-rendered markup verbatim.
     *
     * The explicit opt-out from escaping. Only pass markup this library produced,
     * or content a template engine has already escaped.
     */
    public function html(string $value): self
    {
        $this->guardAgainstContent();
        $this->children[] = $value;

        return $this;
    }

    /**
     * Append child elements, in order. null children are dropped, so an optional
     * element can be expressed as a conditional at the call site.
     */
    public function children(?self ...$children): self
    {
        foreach ($children as $child) {
            if ($child === null) {
                continue;
            }

            $this->guardAgainstContent();
            $this->children[] = $child->render();
        }

        return $this;
    }

    public function render(): string
    {
        $open = '<' . $this->tag . $this->renderAttributes() . '>';

        if ($this->isVoid()) {
            return $open;
        }

        return $open . implode('', $this->children) . '</' . $this->tag . '>';
    }

    private function isVoid(): bool
    {
        return in_array($this->tag, self::VOID_ELEMENTS, true);
    }

    private function guardAgainstContent(): void
    {
        if ($this->isVoid()) {
            throw new InvalidArgumentException(
                sprintf('Void element "%s" cannot have content.', $this->tag),
            );
        }
    }

    private function renderAttributes(): string
    {
        $rendered = '';

        foreach ($this->attributes as $name => $value) {
            $rendered .= $value === true
                ? ' ' . $name
                : ' ' . $name . '="' . self::escape($value) . '"';
        }

        return $rendered;
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
