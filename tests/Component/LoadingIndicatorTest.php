<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\LoadingIndicator;
use Guild\Rivet\Enum\LoaderSize;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoadingIndicator::class)]
final class LoadingIndicatorTest extends TestCase
{
    public function testTheDefaultLoaderCarriesAnAccessibleName(): void
    {
        self::assertSame(
            '<div class="rvt-loader" aria-label="Content loading"></div>',
            new LoadingIndicator()->render(new RenderContext()),
            'A loader conveys state, so it needs a name even though it has no text.',
        );
    }

    public function testSizeIsAModifier(): void
    {
        self::assertStringContainsString(
            'class="rvt-loader rvt-loader--xs"',
            new LoadingIndicator(size: LoaderSize::ExtraSmall)->render(new RenderContext()),
            'Rivet abbreviates loader sizes in the class name.',
        );
    }
}
