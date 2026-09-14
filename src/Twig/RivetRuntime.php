<?php

declare(strict_types=1);

namespace Guild\Rivet\Twig;

use Guild\Rivet\Render\RenderFrame;
use Guild\Rivet\Render\Renderer;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Adapter letting compiled Twig templates reach the Renderer.
 *
 * Twig memoises a runtime for the lifetime of the Environment, so this instance — and
 * the render context inside it — is shared by every template that Environment renders.
 * That is why the compiled nodes close in a `finally`: a component throwing mid-body
 * would otherwise leave a stale frame behind and corrupt every later render.
 */
final class RivetRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Renderer $renderer,
    ) {
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    public function leaf(string $name, array $arguments): string
    {
        return $this->renderer->leaf($name, $arguments);
    }

    /**
     * @param  array<array-key, mixed>  $arguments
     */
    public function open(string $name, array $arguments): RenderFrame
    {
        return $this->renderer->open($name, $arguments);
    }

    public function close(RenderFrame $frame): void
    {
        $this->renderer->close($frame);
    }

    public function render(RenderFrame $frame, string $content): string
    {
        return $this->renderer->render($frame, $content);
    }
}
