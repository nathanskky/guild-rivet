<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\ListComponent;
use Guild\Rivet\Enum\ListStyle;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListComponent::class)]
final class ListComponentTest extends TestCase
{
    #[DataProvider('styles')]
    public function testEachStyleHasItsOwnBaseClassRatherThanAModifier(
        ListStyle $style,
        bool $ordered,
        string $expected,
    ): void {
        self::assertSame(
            $expected,
            new ListComponent(style: $style, ordered: $ordered)->render(new RenderContext(), '<li>a</li>'),
            'Rivet spells list variants as separate base classes, not modifiers on one block.',
        );
    }

    /**
     * @return iterable<string, array{ListStyle, bool, string}>
     */
    public static function styles(): iterable
    {
        yield 'unordered' => [ListStyle::Default, false, '<ul class="rvt-list"><li>a</li></ul>'];
        yield 'ordered' => [ListStyle::Default, true, '<ol class="rvt-list"><li>a</li></ol>'];
        yield 'plain' => [ListStyle::Plain, false, '<ul class="rvt-list-plain"><li>a</li></ul>'];
        yield 'inline' => [ListStyle::Inline, false, '<ul class="rvt-list-inline"><li>a</li></ul>'];
        yield 'description' => [ListStyle::Description, false, '<dl class="rvt-list-description"><li>a</li></dl>'];
    }

    public function testADescriptionListIgnoresTheOrderedFlag(): void
    {
        self::assertStringStartsWith(
            '<dl ',
            new ListComponent(style: ListStyle::Description, ordered: true)->render(new RenderContext(), 'x'),
            'A description list has no ordered form, so the flag cannot change its element.',
        );
    }
}
