<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Render\RenderContext;

/**
 * A region of the page written in the body but emitted somewhere else.
 *
 * Each slot hands its content to the enclosing page as the body is captured and renders
 * nothing where it was written, so a stylesheet can be declared next to the markup that
 * needs it and still reach the head. The same mechanism Tabs uses to emit every button
 * before every panel.
 */
abstract class PageSlot extends Component
{
    public static function acceptsContent(): bool
    {
        return true;
    }

    abstract protected function give(Page $page, string $content): void;

    public function render(RenderContext $context, string $content = ''): string
    {
        $this->give($context->requireAncestor(Page::class, static::class), $content);

        return '';
    }
}
