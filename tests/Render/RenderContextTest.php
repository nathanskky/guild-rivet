<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render;

use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\SequentialIdGenerator;
use Guild\Rivet\Test\Render\Fixture\CardStub;
use Guild\Rivet\Test\Render\Fixture\DialogCloseStub;
use Guild\Rivet\Test\Render\Fixture\DialogStub;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RenderContext::class)]
final class RenderContextTest extends TestCase
{
    public function testTheDefaultIdGeneratorIsSequential(): void
    {
        self::assertSame(
            'rvt-alert-1',
            new RenderContext()->ids()->next('rvt-alert'),
            'A context built with no arguments should be usable immediately.',
        );
    }

    public function testAnInjectedIdGeneratorIsUsed(): void
    {
        $ids = new SequentialIdGenerator();
        $ids->next('rvt-alert');

        self::assertSame(
            'rvt-alert-2',
            new RenderContext($ids)->ids()->next('rvt-alert'),
            'Applications composing page fragments need to supply their own generator.',
        );
    }

    public function testThereIsNoAncestorWhenNothingIsOpen(): void
    {
        self::assertNull(
            new RenderContext()->closest(DialogStub::class),
            'An empty stack has no ancestors to find.',
        );
    }

    public function testAnOpenComponentIsFoundByItsDescendant(): void
    {
        $context = new RenderContext();
        $dialog = new DialogStub();
        $context->open($dialog);

        self::assertSame(
            $dialog,
            $context->closest(DialogStub::class),
            'A close button must be able to reach the dialog that owns it.',
        );
    }

    public function testAnAncestorIsFoundThroughInterveningComponents(): void
    {
        $context = new RenderContext();
        $dialog = new DialogStub();
        $context->open($dialog);
        $context->open(new CardStub());

        self::assertSame(
            $dialog,
            $context->closest(DialogStub::class),
            'Nesting an unrelated component between parent and child must not hide the parent.',
        );
    }

    public function testTheNearestAncestorWinsWhenTwoOfTheSameTypeAreOpen(): void
    {
        $context = new RenderContext();
        $context->open(new DialogStub());
        $inner = new DialogStub();
        $context->open($inner);

        self::assertSame(
            $inner,
            $context->closest(DialogStub::class),
            'With nested dialogs a child belongs to the innermost one.',
        );
    }

    public function testClosingRemovesTheComponentFromTheStack(): void
    {
        $context = new RenderContext();
        $context->open(new DialogStub());
        $context->close();

        self::assertNull(
            $context->closest(DialogStub::class),
            'Once a component is closed its descendants are no longer being rendered.',
        );
    }

    public function testRequiringAMissingAncestorThrowsNamingBothComponents(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_dialog_close must be used inside rvt_dialog.');

        new RenderContext()->requireAncestor(DialogStub::class, DialogCloseStub::class);
    }

    public function testRequiringAPresentAncestorReturnsIt(): void
    {
        $context = new RenderContext();
        $dialog = new DialogStub();
        $context->open($dialog);

        self::assertSame(
            $dialog,
            $context->requireAncestor(DialogStub::class, DialogCloseStub::class),
            'The happy path hands back the ancestor so the child can read its id.',
        );
    }
}
