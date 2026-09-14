<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Page;

use Guild\Rivet\Page\RivetAssets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RivetAssets::class)]
final class RivetAssetsTest extends TestCase
{
    public function testUrlsAreBuiltFromTheVersionByDefault(): void
    {
        $assets = new RivetAssets();

        self::assertSame(
            'https://unpkg.com/rivet-core@2.9.1/css/rivet.min.css',
            $assets->coreCss(),
            'The pinned version is the single place a Rivet release is named.',
        );
        self::assertSame('https://unpkg.com/rivet-core@2.9.1/js/rivet.min.js', $assets->coreJs());
        self::assertSame(
            'https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icon-element.css',
            $assets->iconsCss(),
        );
        self::assertSame('https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icons.js', $assets->iconsJs());
    }

    public function testAVersionChangeFlowsIntoEveryUrl(): void
    {
        self::assertStringContainsString(
            'rivet-core@2.8.1',
            new RivetAssets(version: '2.8.1')->coreCss(),
            'An application pinned to an older Rivet must be able to say so once.',
        );
    }

    public function testAnExplicitHrefOverridesTheCdnUrl(): void
    {
        self::assertSame(
            '/assets/rivet.css',
            new RivetAssets(cssHref: '/assets/rivet.css')->coreCss(),
            'Self-hosting must not require turning the feature off entirely.',
        );
    }
}
