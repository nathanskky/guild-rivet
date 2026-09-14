<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\Renderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Renderer::class)]
#[CoversClass(ComponentRegistry::class)]
final class RendererTest extends TestCase
{
    public function testALeafComponentRendersFromItsTemplateName(): void
    {
        self::assertSame(
            '<span class="rvt-badge">New</span>',
            $this->renderer()->leaf('rvt_badge', ['text' => 'New']),
            'A leaf component is rendered in one call, with no content to capture.',
        );
    }

    public function testABlockComponentRendersTheContentCapturedBetweenItsTags(): void
    {
        $renderer = $this->renderer();
        $frame = $renderer->open('rvt_alert', ['title' => 'T', 'dismissible' => false]);
        $renderer->close($frame);

        self::assertStringContainsString(
            '<div class="rvt-alert__message">Body</div>',
            $renderer->render($frame, 'Body'),
            'The engine captures the tag body and hands it back as rendered markup.',
        );
    }

    public function testContentIsTrimmedSoBothEnginesCanAgree(): void
    {
        $renderer = $this->renderer();
        $frame = $renderer->open('rvt_alert', ['title' => 'T', 'dismissible' => false]);
        $renderer->close($frame);

        self::assertStringContainsString(
            '<div class="rvt-alert__message">Body</div>',
            $renderer->render($frame, "\n  Body\n"),
            'Twig swallows a trailing newline after %} and Latte relocates leading indentation, so only trimmed content can match byte for byte.',
        );
    }

    public function testAnOpenComponentIsVisibleToItsDescendantsAndGoneAfterClosing(): void
    {
        $renderer = $this->renderer();
        $frame = $renderer->open('rvt_alert', ['title' => 'T']);

        self::assertNotNull($renderer->context()->closest(Alert::class), 'While the body renders, the alert is the enclosing component.');

        $renderer->close($frame);

        self::assertNull($renderer->context()->closest(Alert::class), 'Once closed it must not be visible to later siblings.');
    }

    public function testClosingIsSafeAfterTheBodyThrows(): void
    {
        $renderer = $this->renderer();
        $frame = $renderer->open('rvt_alert', ['title' => 'T']);
        $renderer->close($frame);
        $renderer->close($frame);

        self::assertNull(
            $renderer->context()->closest(Alert::class),
            'Engine nodes pop in a finally block, so a double close must not unbalance the stack.',
        );
    }

    public function testAnUnknownComponentNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no Rivet component named "rvt_nope".');

        $this->renderer()->leaf('rvt_nope', []);
    }

    public function testResetStartsIdNumberingOver(): void
    {
        $renderer = $this->renderer();
        $renderer->render($renderer->open('rvt_alert', ['title' => 'A']), 'x');
        $renderer->reset();

        self::assertStringContainsString(
            'data-rvt-alert="rvt-alert-1"',
            $renderer->render($renderer->open('rvt_alert', ['title' => 'B']), 'x'),
            'Each page render starts a fresh context so output is reproducible.',
        );
    }

    public function testRequiringAMissingAncestorFailsAtRenderTime(): void
    {
        $this->expectException(ComponentContextException::class);

        $this->renderer()->context()->requireAncestor(Alert::class, Badge::class);
    }

    private function renderer(): Renderer
    {
        return new Renderer(new ComponentRegistry([
            Badge::class,
            Alert::class,
        ]));
    }
}
