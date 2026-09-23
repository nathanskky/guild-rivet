<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Page\NavigationProvider;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Rivet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RenderContext::class)]
#[CoversClass(Renderer::class)]
#[CoversClass(Page::class)]
final class NavigationProviderTest extends TestCase
{
    public function testWithoutAProviderTheConfiguredItemsAreUsed(): void
    {
        $defaults = $this->defaults();

        self::assertSame(
            $defaults->navItems,
            new RenderContext(pageDefaults: $defaults)->navItems($defaults),
            'An application that supplies no provider gets exactly the items it configured.',
        );
    }

    public function testAProviderReplacesTheItemsInTheRenderedHeader(): void
    {
        $html = new Page()->render(new RenderContext(pageDefaults: $this->defaults(), navigation: $this->provider()));

        self::assertStringContainsString('>Courses</a>', $html, 'The provider receives the configured items and can keep them.');
        self::assertStringContainsString('>Settings</a>', $html, 'An item the provider adds is rendered in the header.');
    }

    public function testTheProviderIsAskedOnlyWhenAPageRenders(): void
    {
        $provider = $this->provider();
        $renderer = new Renderer(Rivet::registry(), pageDefaults: $this->defaults(), navigation: $provider);

        $renderer->leaf('rvt_badge', ['text' => 'New']);

        self::assertSame(0, $provider->calls, 'Rendering a component that is not a page never asks for navigation.');

        $frame = $renderer->open('rvt_page', []);
        $renderer->close($frame);
        $renderer->render($frame, '');

        self::assertSame(1, $provider->calls, 'Rendering a page asks once, which is when per-viewer items are worked out.');
    }

    public function testTheProviderSurvivesAReset(): void
    {
        $renderer = new Renderer(new ComponentRegistry([]), pageDefaults: $this->defaults(), navigation: $this->provider());

        $renderer->reset();

        self::assertCount(
            2,
            $renderer->context()->navItems($this->defaults()),
            'Every page restarts the context, and each fresh context keeps the provider.',
        );
    }

    private function defaults(): PageDefaults
    {
        return new PageDefaults(appTitle: 'Course Catalog', navItems: [['label' => 'Courses', 'href' => '/courses']]);
    }

    private function provider(): NavigationProvider
    {
        return new class () implements NavigationProvider {
            public int $calls = 0;

            public function navItems(PageDefaults $defaults): array
            {
                $this->calls++;

                return [...$defaults->navItems, ['label' => 'Settings', 'href' => '/settings']];
            }
        };
    }
}
