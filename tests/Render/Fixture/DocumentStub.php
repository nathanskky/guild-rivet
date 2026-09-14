<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render\Fixture;

use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\StartsRender;

/**
 * A component that begins a render, without the Page component's markup concerns.
 */
final class DocumentStub extends StubComponent implements StartsRender
{
    public static function name(): string
    {
        return 'rvt_document';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return $content;
    }
}
