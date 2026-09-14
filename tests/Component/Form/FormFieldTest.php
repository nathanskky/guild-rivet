<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Form;

use Guild\Rivet\Component\Form\FormField;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormField::class)]
final class FormFieldTest extends TestCase
{
    public function testALabelIsBoundToTheControlItNames(): void
    {
        self::assertSame(
            '<label class="rvt-label" for="rvt-field-1">Username</label><input>',
            new FormField('Username')->render(new RenderContext(), '<input>'),
            'Rivet requires an explicit for/id pairing rather than wrapping the control.',
        );
    }

    public function testNoWrapperElementIsAdded(): void
    {
        self::assertStringNotContainsString(
            '<div class="rvt-form-field"',
            new FormField('Username')->render(new RenderContext(), '<input>'),
            'Rivet documents label, control and message as siblings, with no wrapper to invent.',
        );
    }

    public function testARequiredFieldMarksItsLabel(): void
    {
        self::assertStringContainsString(
            'Username <span class="rvt-color-orange-500 rvt-text-bold">*</span></label>',
            new FormField('Username', required: true)->render(new RenderContext(), '<input>'),
            'Rivet spells the required marker as manual markup, which the component should supply.',
        );
    }

    public function testAVisuallyHiddenLabelIsStillReadable(): void
    {
        self::assertStringContainsString(
            '<label class="rvt-label rvt-sr-only" for="rvt-field-1">Search</label>',
            new FormField('Search', labelHidden: true)->render(new RenderContext(), '<input>'),
            'Hiding a label visually must not remove it.',
        );
    }

    public function testHelperTextIsRenderedAndDescribesTheField(): void
    {
        $field = new FormField('Username', helperText: 'Letters and numbers only.');
        $html = $field->render(new RenderContext(), '<input>');

        self::assertStringContainsString('id="rvt-field-1-helper"', $html);
        self::assertSame(
            'rvt-field-1-helper',
            $field->describedBy(),
            'The control points aria-describedby at whichever sections actually rendered.',
        );
    }

    public function testErrorsAreRenderedAsInlineAlerts(): void
    {
        $html = new FormField('Username', errors: ['That username is taken.'])
            ->render(new RenderContext(), '<input>');

        self::assertStringContainsString('class="rvt-inline-alert rvt-inline-alert--danger"', $html);
        self::assertStringContainsString('id="rvt-field-1-error-1"', $html);
        self::assertStringContainsString('That username is taken.', $html);
    }

    public function testDescribedByListsEverySectionThatRendered(): void
    {
        $field = new FormField('U', helperText: 'Hint.', errors: ['One.', 'Two.']);
        $field->render(new RenderContext(), '<input>');

        self::assertSame(
            'rvt-field-1-helper rvt-field-1-error-1 rvt-field-1-error-2',
            $field->describedBy(),
            'Only sections that exist may be referenced; a dangling id reads as nothing to a screen reader.',
        );
    }

    public function testDescribedByIsEmptyWhenThereIsNothingToDescribe(): void
    {
        $field = new FormField('U');
        $field->render(new RenderContext(), '<input>');

        self::assertNull($field->describedBy(), 'A field with no helper or error needs no aria-describedby at all.');
    }

    public function testAnExplicitIdIsUsedForTheWholeField(): void
    {
        $html = new FormField('U', id: 'username', helperText: 'H.')->render(new RenderContext(), '<input>');

        self::assertStringContainsString('for="username"', $html);
        self::assertStringContainsString('id="username-helper"', $html, 'Every derived id follows the one the caller chose.');
    }

    public function testTheFieldReportsWhetherItIsInvalid(): void
    {
        self::assertTrue(new FormField('U', errors: ['x'])->hasErrors());
        self::assertFalse(new FormField('U')->hasErrors());
    }
}
