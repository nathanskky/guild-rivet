<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Dialog;

use Guild\Rivet\Component\Dialog\Dialog;
use Guild\Rivet\Component\Dialog\DialogBody;
use Guild\Rivet\Component\Dialog\DialogControls;
use Guild\Rivet\Enum\DialogPosition;
use Guild\Rivet\Enum\DialogVariant;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dialog::class)]
#[CoversClass(DialogBody::class)]
#[CoversClass(DialogControls::class)]
final class DialogTest extends TestCase
{
    public function testOneIdentifierReachesEveryPlaceRivetNeedsIt(): void
    {
        $html = new Dialog(title: 'Confirm', triggerText: 'Open')->render(new RenderContext(), 'body');

        foreach ([
            'data-rvt-dialog-trigger="rvt-dialog-1"',
            'id="rvt-dialog-1"',
            'data-rvt-dialog="rvt-dialog-1"',
            'data-rvt-dialog-close="rvt-dialog-1"',
            'aria-labelledby="rvt-dialog-1-title"',
            'aria-describedby="rvt-dialog-1-description"',
        ] as $wiring) {
            self::assertStringContainsString(
                $wiring,
                $html,
                'Rivet needs the same identifier in six places; the documentation hard-codes it, so two dialogs from the docs collide.',
            );
        }
    }

    public function testTwoDialogsOnOnePageDoNotCollide(): void
    {
        $context = new RenderContext();
        new Dialog(title: 'One')->render($context, 'a');

        self::assertStringContainsString(
            'data-rvt-dialog="rvt-dialog-2"',
            new Dialog(title: 'Two')->render($context, 'b'),
        );
    }

    public function testADialogStartsHiddenAndIsFocusable(): void
    {
        $html = new Dialog(title: 'T')->render(new RenderContext(), 'b');

        self::assertStringContainsString('role="dialog"', $html);
        self::assertStringContainsString('tabindex="-1"', $html, 'Rivet moves focus into the dialog, which needs it focusable.');
        self::assertStringContainsString('hidden', $html, 'A dialog is closed until its JavaScript opens it.');
    }

    public function testTheDefaultVariantDisablesPageInteractionWithoutBeingModal(): void
    {
        $html = new Dialog(title: 'T')->render(new RenderContext(), 'b');

        self::assertStringContainsString('data-rvt-dialog-disable-page-interaction', $html);
        self::assertStringNotContainsString('data-rvt-dialog-modal', $html);
        self::assertStringNotContainsString('data-rvt-dialog-darken-page', $html);
    }

    public function testTheModalVariantTurnsOnAllThreeBehaviours(): void
    {
        $html = new Dialog(title: 'T', variant: DialogVariant::Modal)->render(new RenderContext(), 'b');

        self::assertStringContainsString('data-rvt-dialog-modal', $html);
        self::assertStringContainsString('data-rvt-dialog-darken-page', $html);
        self::assertStringContainsString('data-rvt-dialog-disable-page-interaction', $html);
    }

    public function testAnExplicitFlagOverridesTheVariantDefault(): void
    {
        self::assertStringNotContainsString(
            'data-rvt-dialog-darken-page',
            new Dialog(title: 'T', variant: DialogVariant::Modal, darkenPage: false)->render(new RenderContext(), 'b'),
            'null means inherit from the variant; a stated value is the caller overriding it.',
        );
    }

    public function testANotificationOpensItselfInTheTopRight(): void
    {
        $html = new Dialog(title: 'T', variant: DialogVariant::Notification)->render(new RenderContext(), 'b');

        self::assertStringContainsString('data-rvt-dialog-open-on-init', $html);
        self::assertStringContainsString('data-rvt-dialog-top-right', $html, 'A variant carries a default position as well as default behaviour.');
    }

    public function testPositionCanBeSetIndependently(): void
    {
        self::assertStringContainsString(
            'data-rvt-dialog-bottom-left',
            new Dialog(title: 'T', position: DialogPosition::BottomLeft)->render(new RenderContext(), 'b'),
        );
    }

    public function testAHelpWidgetIsNamedDirectlyBecauseItHasNoTitleBar(): void
    {
        $html = new Dialog(title: 'Need help?', variant: DialogVariant::HelpWidget)->render(new RenderContext(), 'b');

        self::assertStringContainsString('aria-label="Need help?"', $html);
        self::assertStringNotContainsString('rvt-dialog__header', $html);
        self::assertStringNotContainsString('aria-labelledby', $html, 'There is no title element to point at.');
    }

    public function testTheCloseButtonCanBeOmitted(): void
    {
        self::assertStringNotContainsString(
            'rvt-dialog__close',
            new Dialog(title: 'T', showClose: false)->render(new RenderContext(), 'b'),
            'A confirmation dialog deliberately forces a choice rather than offering a way out.',
        );
    }

    public function testTheBodyCarriesTheDescriptionIdTheDialogPointsAt(): void
    {
        $context = new RenderContext();
        $context->open(new Dialog(title: 'T'));

        self::assertSame(
            '<div class="rvt-dialog__body" id="rvt-dialog-1-description"><p>Are you sure?</p></div>',
            new DialogBody()->render($context, '<p>Are you sure?</p>'),
            'The body supplies the element the dialog describes itself with.',
        );
    }

    public function testControlsAreWrappedForRivetToLayOut(): void
    {
        $context = new RenderContext();
        $context->open(new Dialog(title: 'T'));

        self::assertSame(
            '<div class="rvt-dialog__controls">buttons</div>',
            new DialogControls()->render($context, 'buttons'),
        );
    }

    public function testDialogPartsOutsideADialogAreRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_dialog_body must be used inside rvt_dialog.');

        new DialogBody()->render(new RenderContext(), 'x');
    }
}
