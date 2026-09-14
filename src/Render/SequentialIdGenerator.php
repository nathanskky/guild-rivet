<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

/**
 * Numbers ids sequentially per prefix: rvt-dialog-1, rvt-dialog-2, rvt-alert-1.
 *
 * Deterministic by design. Golden-file fixtures can therefore assert exact ids with no
 * placeholder patterns, and the cross-engine parity test can compare Twig and Latte
 * output byte for byte.
 */
final class SequentialIdGenerator implements IdGenerator
{
    /** @var array<string, int> */
    private array $counters = [];

    public function next(string $prefix): string
    {
        $count = ($this->counters[$prefix] ?? 0) + 1;
        $this->counters[$prefix] = $count;

        return $prefix . '-' . $count;
    }
}
