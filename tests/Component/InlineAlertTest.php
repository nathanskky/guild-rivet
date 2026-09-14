<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\InlineAlert;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineAlert::class)]
final class InlineAlertTest extends TestCase
{
    public function testAnInlineAlertPairsAnIconWithAnIdentifiedMessage(): void
    {
        self::assertSame(
            '<div class="rvt-inline-alert rvt-inline-alert--danger">'
            . '<span class="rvt-inline-alert__icon">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">'
            . '<path d="m8 6.586-2-2L4.586 6l2 2-2 2L6 11.414l2-2 2 2L11.414 10l-2-2 2-2L10 4.586l-2 2Z"/>'
            . '<path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0ZM2 8a6 6 0 1 1 12 0A6 6 0 0 1 2 8Z"/>'
            . '</svg></span>'
            . '<span class="rvt-inline-alert__message" id="rvt-inline-alert-1">That username is taken.</span>'
            . '</div>',
            new InlineAlert('That username is taken.', style: AlertStyle::Danger)->render(new RenderContext()),
            'The message carries an id so a form field can point aria-describedby at it.',
        );
    }

    #[DataProvider('severities')]
    public function testEachSeverityHasItsOwnGlyph(AlertStyle $style, string $glyph): void
    {
        self::assertStringContainsString(
            $glyph,
            new InlineAlert('m', style: $style)->render(new RenderContext()),
            'Colour alone cannot carry meaning, so each severity has a distinct icon.',
        );
    }

    /**
     * @return iterable<string, array{AlertStyle, string}>
     */
    public static function severities(): iterable
    {
        yield 'info' => [AlertStyle::Info, 'M9 7v5H7V7h2Z'];
        yield 'success' => [AlertStyle::Success, 'M7 11.414 11.914 6.5'];
        yield 'warning' => [AlertStyle::Warning, 'M12 7H4v2h8V7Z'];
        yield 'danger' => [AlertStyle::Danger, 'm8 6.586-2-2L4.586 6'];
    }

    public function testTheStandaloneFormAddsItsModifier(): void
    {
        self::assertStringContainsString(
            'class="rvt-inline-alert rvt-inline-alert--standalone rvt-inline-alert--info"',
            new InlineAlert('m', standalone: true)->render(new RenderContext()),
            'Standalone gives the alert a background when it is not sitting under a field.',
        );
    }

    public function testTheMessageIdCanBeSetSoAFieldCanReferenceIt(): void
    {
        self::assertStringContainsString(
            'id="username-error"',
            new InlineAlert('m', id: 'username-error')->render(new RenderContext()),
            'A form field needs a known id to describe itself with.',
        );
    }
}
