<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render;

use Guild\Rivet\Render\SequentialIdGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SequentialIdGenerator::class)]
final class SequentialIdGeneratorTest extends TestCase
{
    public function testTheFirstIdForAPrefixIsNumberedOne(): void
    {
        self::assertSame(
            'rvt-dialog-1',
            new SequentialIdGenerator()->next('rvt-dialog'),
            'Ids start at 1 so fixtures read naturally.',
        );
    }

    public function testEachCallForTheSamePrefixIncrements(): void
    {
        $ids = new SequentialIdGenerator();

        self::assertSame('rvt-dialog-1', $ids->next('rvt-dialog'));
        self::assertSame('rvt-dialog-2', $ids->next('rvt-dialog'));
        self::assertSame('rvt-dialog-3', $ids->next('rvt-dialog'), 'Two dialogs on one page must never share an id.');
    }

    public function testPrefixesAreCountedIndependently(): void
    {
        $ids = new SequentialIdGenerator();
        $ids->next('rvt-dialog');

        self::assertSame(
            'rvt-alert-1',
            $ids->next('rvt-alert'),
            'An unrelated component must not have its numbering pushed along by another.',
        );
    }

    public function testASeparateInstanceStartsOver(): void
    {
        new SequentialIdGenerator()->next('rvt-dialog');

        self::assertSame(
            'rvt-dialog-1',
            new SequentialIdGenerator()->next('rvt-dialog'),
            'Counters are per-render, so output is deterministic and comparable across engines.',
        );
    }
}
