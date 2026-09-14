<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Icon;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Icon::class)]
final class IconTest extends TestCase
{
    public function testADecorativeIconIsHiddenFromAssistiveTechnology(): void
    {
        self::assertSame(
            '<rvt-icon name="plus" aria-hidden="true"></rvt-icon>',
            new Icon('plus')->render(new RenderContext()),
            'An icon with no label is decorative: the surrounding control carries the name.',
        );
    }

    public function testALabelledIconIsExposedAsAnImage(): void
    {
        self::assertSame(
            '<rvt-icon name="alarm" role="img" aria-label="Overdue"></rvt-icon>',
            new Icon('alarm', label: 'Overdue')->render(new RenderContext()),
            'An icon carrying meaning needs a role and a name, and must not be hidden.',
        );
    }

    public function testTheIconNameIsEscaped(): void
    {
        self::assertStringContainsString(
            'name="a&quot;b"',
            new Icon('a"b')->render(new RenderContext()),
            'The name reaches an attribute, so it must be escaped.',
        );
    }
}
