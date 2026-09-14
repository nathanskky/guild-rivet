<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

/**
 * Supplies unique element ids for components that need one.
 *
 * Swappable because the right strategy depends on how a page is assembled. The default
 * counts sequentially, which keeps output deterministic for tests and diffs. An
 * application that composes independently rendered fragments into one page — htmx
 * partials, edge-side includes — should inject a random implementation instead, since
 * two fragments rendered separately would otherwise restart the same sequence.
 */
interface IdGenerator
{
    /**
     * Return an id that has not been returned before by this instance.
     */
    public function next(string $prefix): string;
}
