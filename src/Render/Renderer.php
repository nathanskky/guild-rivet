<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

use Guild\Rivet\Component\Component;

/**
 * The entry point both engine integrations compile their tags down to.
 *
 * Leaf components render in a single call. Block components are opened before their body
 * is evaluated — so descendants can reach them — then closed and rendered with the
 * captured markup.
 */
final class Renderer
{
    private RenderContext $context;

    public function __construct(
        private readonly ComponentRegistry $registry,
        private readonly ComponentFactory $factory = new ComponentFactory(),
    ) {
        $this->context = new RenderContext();
    }

    public function context(): RenderContext
    {
        return $this->context;
    }

    /**
     * Begin a fresh render: empty stack, id numbering back to one.
     *
     * Call this once per page. Ids stay unique without it, but they keep climbing for
     * the life of the process, so the same page would not render identically twice.
     * There is no hook in either engine that reliably signals the start of a top-level
     * render, so the integration has to say when.
     */
    public function reset(?IdGenerator $ids = null): void
    {
        $this->context = new RenderContext($ids);
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    public function leaf(string $name, array $arguments): string
    {
        return $this->component($name, $arguments)->render($this->context);
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    public function open(string $name, array $arguments): RenderFrame
    {
        $component = $this->component($name, $arguments);
        $this->context->open($component);

        return new RenderFrame($component);
    }

    public function close(RenderFrame $frame): void
    {
        if ($frame->close()) {
            $this->context->close();
        }
    }

    /**
     * Render an opened component around the body captured between its tags.
     *
     * The body is trimmed because the engines disagree on surrounding whitespace: Twig
     * swallows one newline after `%}` while Latte moves leading indentation into the
     * following element, across the capture boundary. Trimming is what makes identical
     * output across the two achievable at all.
     */
    public function render(RenderFrame $frame, string $content): string
    {
        return $frame->component->render($this->context, trim($content));
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    private function component(string $name, array $arguments): Component
    {
        return $this->factory->create($this->registry->classFor($name), $arguments);
    }
}
