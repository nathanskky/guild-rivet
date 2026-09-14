<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Test\Render\Fixture\DocumentStub;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Renderer::class)]
#[CoversClass(RenderContext::class)]
final class StartsRenderTest extends TestCase
{
    public function testTheContextCarriesTheConfiguredDefaults(): void
    {
        $defaults = new PageDefaults(appTitle: 'Course Catalog');

        self::assertSame(
            $defaults,
            new RenderContext(pageDefaults: $defaults)->pageDefaults(),
            'A page reads application-wide configuration from the context, as it does the id generator.',
        );
    }

    public function testAContextWithNoDefaultsSaysSo(): void
    {
        self::assertNull(new RenderContext()->pageDefaults());
    }

    public function testOpeningAPageResetsIdNumbering(): void
    {
        $renderer = $this->renderer();
        $alert = $renderer->open('rvt_alert', ['title' => 'Before']);
        $renderer->render($alert, 'x');
        $renderer->close($alert);

        $frame = $renderer->open('rvt_document', []);

        self::assertSame(
            'rvt-alert-1',
            $renderer->context()->ids()->next('rvt-alert'),
            'A page is the start of a render, so numbering begins again and the same page always emits the same ids.',
        );
        $renderer->close($frame);
    }

    public function testTheResetPreservesTheConfiguredDefaults(): void
    {
        $defaults = new PageDefaults(appTitle: 'Course Catalog');
        $renderer = $this->renderer($defaults);

        $frame = $renderer->open('rvt_document', []);

        self::assertSame(
            $defaults,
            $renderer->context()->pageDefaults(),
            'Resetting starts a fresh render, not a fresh application.',
        );
        $renderer->close($frame);
    }

    public function testAPageInsideAnotherComponentIsRejected(): void
    {
        $renderer = $this->renderer();
        $renderer->open('rvt_alert', ['title' => 'Outer']);

        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_document cannot be nested inside another component.');

        $renderer->open('rvt_document', []);
    }

    private function renderer(?PageDefaults $defaults = null): Renderer
    {
        return new Renderer(
            new ComponentRegistry([Alert::class, Badge::class, DocumentStub::class]),
            pageDefaults: $defaults,
        );
    }
}
