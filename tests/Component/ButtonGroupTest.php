<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\ButtonGroup;
use Guild\Rivet\Component\SegmentedButtons;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ButtonGroup::class)]
#[CoversClass(SegmentedButtons::class)]
final class ButtonGroupTest extends TestCase
{
    public function testAButtonGroupWrapsItsButtons(): void
    {
        self::assertSame(
            '<div class="rvt-button-group">buttons</div>',
            new ButtonGroup()->render(new RenderContext(), 'buttons'),
            'A plain group only arranges the buttons inside it.',
        );
    }

    public function testAGroupCanBeRightAligned(): void
    {
        self::assertStringContainsString(
            'class="rvt-button-group rvt-button-group--right"',
            new ButtonGroup(alignRight: true)->render(new RenderContext(), 'x'),
            'Right alignment is the usual choice for form actions.',
        );
    }

    public function testSegmentedButtonsAreANamedGroup(): void
    {
        self::assertSame(
            '<div class="rvt-button-segmented" role="group" aria-label="View mode">buttons</div>',
            new SegmentedButtons(label: 'View mode')->render(new RenderContext(), 'buttons'),
            'Segmented buttons act as one control, so the grouping needs a role and a name.',
        );
    }

    public function testSegmentedButtonsCanFillTheirContainer(): void
    {
        self::assertStringContainsString(
            'class="rvt-button-segmented rvt-button-segmented--fitted"',
            new SegmentedButtons(label: 'x', fitted: true)->render(new RenderContext(), 'y'),
        );
    }

    public function testSegmentedButtonsWithoutALabelAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_segmented_buttons requires a label naming the group.');

        new SegmentedButtons(label: '')->render(new RenderContext(), 'x');
    }
}
