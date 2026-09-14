<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Table;
use Guild\Rivet\Enum\TableStyle;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Table::class)]
final class TableTest extends TestCase
{
    public function testACaptionIsAlwaysEmittedAndHiddenByDefault(): void
    {
        self::assertSame(
            '<table class="rvt-table"><caption class="rvt-sr-only" id="rvt-table-1-caption">Services</caption><tr></tr></table>',
            new Table(caption: 'Services')->render(new RenderContext(), '<tr></tr>'),
            'Rivet requires a caption; hiding it visually keeps it available to screen readers.',
        );
    }

    public function testAVisibleCaptionDropsTheScreenReaderOnlyClass(): void
    {
        self::assertStringContainsString(
            '<caption id="rvt-table-1-caption">Services</caption>',
            new Table(caption: 'Services', captionVisible: true)->render(new RenderContext(), 'x'),
            'A caption can be shown when it helps sighted readers too.',
        );
    }

    public function testStylesAreSeparateBaseClasses(): void
    {
        self::assertStringContainsString(
            '<table class="rvt-table-stripes">',
            new Table(caption: 'c', style: TableStyle::Stripes)->render(new RenderContext(), 'x'),
            'Rivet spells table styles as alternative base classes, not modifiers.',
        );
    }

    public function testAResponsiveTableIsWrappedInALabelledScrollRegion(): void
    {
        $html = new Table(caption: 'Services', responsive: true)->render(new RenderContext(), 'x');

        self::assertStringStartsWith(
            '<div class="rvt-table-responsive" role="region" tabindex="0" aria-labelledby="rvt-table-1-caption">',
            $html,
            'A horizontally scrolling region must be focusable and named.',
        );
        self::assertStringContainsString(
            'id="rvt-table-1-caption"',
            $html,
            'The wrapper is named by the caption, so the two ids must be generated together.',
        );
    }

    public function testTwoTablesOnOnePageGetDistinctCaptionIds(): void
    {
        $context = new RenderContext();
        new Table(caption: 'A', responsive: true)->render($context, 'x');

        self::assertStringContainsString(
            'aria-labelledby="rvt-table-2-caption"',
            new Table(caption: 'B', responsive: true)->render($context, 'x'),
            'Rivet documentation explicitly warns that multiple responsive tables need unique ids.',
        );
    }
}
