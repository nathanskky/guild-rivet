<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render\Fixture;

use Guild\Rivet\Component\Component;

/**
 * Stand-in for a real component in RenderContext tests.
 *
 * Real components carry markup concerns the context knows nothing about; these
 * subclasses exist only to give the stack distinct types to search for.
 */
abstract class StubComponent extends Component
{
    public function render(string $content = ''): string
    {
        return $content;
    }
}
