<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

use Guild\Rivet\Component\Component;

/**
 * A block component that is currently open, handed back to the engine node that opened
 * it so the same instance can be closed and then rendered with its captured body.
 *
 * Closing is idempotent. Engine nodes pop in a `finally`, which can run after an
 * explicit close on some paths, and an unbalanced stack would corrupt every later render
 * in the same process.
 */
final class RenderFrame
{
    private bool $closed = false;

    public function __construct(
        public readonly Component $component,
    ) {
    }

    /**
     * @return bool whether this call was the one that closed the frame
     */
    public function close(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return true;
    }
}
