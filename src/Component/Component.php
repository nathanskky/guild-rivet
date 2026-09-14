<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Render\RenderContext;

/**
 * Base class for every Rivet component.
 *
 * Components are plain PHP objects. They know nothing about Twig or Latte, so they are
 * equally usable from a controller, a mailer, or a test.
 */
abstract class Component
{
    /**
     * Cached so re-rendering the same instance does not consume a second id and change
     * the markup it produced the first time.
     */
    private ?string $resolvedId = null;


    /**
     * The component's canonical name in snake_case, such as `rvt_dialog_close`.
     *
     * Each engine derives its own tag spelling from this, and it is what appears in
     * error messages, so it should read the way a developer writes it in a template.
     */
    abstract public static function name(): string;

    /**
     * Render this component to HTML.
     *
     * The context supplies element ids and access to enclosing components. `$content` is
     * the already-rendered inner markup for components that take children, and is empty
     * for those that do not.
     */
    /**
     * Whether this component wraps content written between its tags.
     *
     * Decides whether each engine exposes it as a paired tag or a single one.
     */
    public static function acceptsContent(): bool
    {
        return false;
    }

    abstract public function render(RenderContext $context, string $content = ''): string;

    /**
     * Settle this component's element id: the caller's if given, otherwise a generated one.
     *
     * Resolved once per instance. Sub-ids are derived from the result by suffixing
     * (`{id}-title`, `{id}-description`), which is what keeps the several attributes
     * Rivet requires from drifting apart.
     */
    protected function resolveId(RenderContext $context, string $prefix, ?string $explicit): string
    {
        return $this->resolvedId ??= $explicit ?? $context->ids()->next($prefix);
    }
}
