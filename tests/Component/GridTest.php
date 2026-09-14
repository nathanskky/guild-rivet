<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Grid\Column;
use Guild\Rivet\Component\Grid\Container;
use Guild\Rivet\Component\Grid\Row;
use Guild\Rivet\Enum\ContainerSize;
use Guild\Rivet\Enum\RowSpacing;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
#[CoversClass(Row::class)]
#[CoversClass(Column::class)]
final class GridTest extends TestCase
{
    public function testAContainerCarriesItsSizeInTheClassName(): void
    {
        self::assertSame(
            '<div class="rvt-container-lg">x</div>',
            new Container(size: ContainerSize::Large)->render(new RenderContext(), 'x'),
            'Rivet spells container widths as separate classes rather than modifiers.',
        );
    }

    public function testRowSpacingIsAModifier(): void
    {
        self::assertSame(
            '<div class="rvt-row rvt-row--loose">x</div>',
            new Row(spacing: RowSpacing::Loose)->render(new RenderContext(), 'x'),
        );
    }

    public function testAColumnWithNoWidthSharesEquallyWithItsSiblings(): void
    {
        self::assertSame(
            '<div class="rvt-cols">x</div>',
            new Column()->render(new RenderContext(), 'x'),
            'An unsized column is the auto-width form.',
        );
    }

    public function testAColumnWidthAppliesFromTheMediumBreakpointUp(): void
    {
        self::assertSame(
            '<div class="rvt-cols-4-md">x</div>',
            new Column(width: 4)->render(new RenderContext(), 'x'),
            'Rivet columns stack below the medium breakpoint, so widths are scoped to -md.',
        );
    }

    public function testPushPullAndLastCompose(): void
    {
        self::assertSame(
            '<div class="rvt-cols-8-md rvt-cols-push-8-md rvt-cols-pull-4-md rvt-cols--last">x</div>',
            new Column(width: 8, push: 8, pull: 4, last: true)->render(new RenderContext(), 'x'),
            'Source order can differ from visual order, which is what push and pull are for.',
        );
    }

    public function testAWidthOutsideTheTwelveColumnGridIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Column width must be between 1 and 12, 13 given.');

        new Column(width: 13)->render(new RenderContext(), 'x');
    }
}
