<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Badge;
use Guild\Rivet\Enum\BadgeStyle;
use Guild\Rivet\Enum\BadgeVariant;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Badge::class)]
final class BadgeTest extends TestCase
{
    public function testADefaultBadgeRendersTheBaseClassOnly(): void
    {
        self::assertSame(
            '<span class="rvt-badge">Base</span>',
            $this->render(new Badge('Base')),
            'The default style adds no modifier, matching Rivet base badge markup.',
        );
    }

    public function testBadgeTextIsEscaped(): void
    {
        self::assertSame(
            '<span class="rvt-badge">Fish &amp; Chips</span>',
            $this->render(new Badge('Fish & Chips')),
            'Badge text is caller data and must never be able to inject markup.',
        );
    }

    #[DataProvider('styleAndVariantCombinations')]
    public function testStyleAndVariantComposeIntoOneModifier(
        BadgeStyle $style,
        BadgeVariant $variant,
        string $expectedClass,
    ): void {
        self::assertSame(
            sprintf('<span class="%s">x</span>', $expectedClass),
            $this->render(new Badge('x', style: $style, variant: $variant)),
            'Style and variant are independent axes composed into a single Rivet class.',
        );
    }

    /**
     * @return iterable<string, array{BadgeStyle, BadgeVariant, string}>
     */
    public static function styleAndVariantCombinations(): iterable
    {
        yield 'base solid' => [BadgeStyle::Base, BadgeVariant::Solid, 'rvt-badge'];
        yield 'base secondary' => [BadgeStyle::Base, BadgeVariant::Secondary, 'rvt-badge rvt-badge--secondary'];
        yield 'info solid' => [BadgeStyle::Info, BadgeVariant::Solid, 'rvt-badge rvt-badge--info'];
        yield 'info secondary' => [BadgeStyle::Info, BadgeVariant::Secondary, 'rvt-badge rvt-badge--info-secondary'];
        yield 'danger secondary' => [BadgeStyle::Danger, BadgeVariant::Secondary, 'rvt-badge rvt-badge--danger-secondary'];
    }

    public function testCallerAttributesAreMergedAfterComponentClasses(): void
    {
        self::assertSame(
            '<span class="rvt-badge rvt-badge--info rvt-m-left-xs" data-testid="b">New</span>',
            $this->render(new Badge('New', style: BadgeStyle::Info, extra: new Attributes([
                'class' => 'rvt-m-left-xs',
                'data_testid' => 'b',
            ]))),
            'The escape hatch lets a caller add utility classes without losing the component classes.',
        );
    }

    private function render(Badge $badge): string
    {
        return $badge->render(new RenderContext());
    }
}
