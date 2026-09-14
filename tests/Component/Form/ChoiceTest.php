<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Form;

use Guild\Rivet\Component\Form\Checkbox;
use Guild\Rivet\Component\Form\FieldGroup;
use Guild\Rivet\Component\Form\Radio;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FieldGroup::class)]
#[CoversClass(Checkbox::class)]
#[CoversClass(Radio::class)]
final class ChoiceTest extends TestCase
{
    public function testAGroupIsAFieldsetWithALegendAndAPlainList(): void
    {
        self::assertSame(
            '<fieldset class="rvt-fieldset"><legend>Campus</legend>'
            . '<ul class="rvt-list-plain">items</ul>'
            . '</fieldset>',
            new FieldGroup('Campus')->render(new RenderContext(), 'items'),
            'Rivet groups choices in a fieldset whose options sit in an unstyled list.',
        );
    }

    public function testALegendCanBeHiddenButNotRemoved(): void
    {
        self::assertStringContainsString(
            '<legend class="rvt-sr-only">Campus</legend>',
            new FieldGroup('Campus', legendHidden: true)->render(new RenderContext(), 'x'),
            'A visible heading may already name the group, but the fieldset still needs its legend.',
        );
    }

    public function testAnInlineGroupUsesTheInlineList(): void
    {
        self::assertStringContainsString(
            '<ul class="rvt-list-inline">',
            new FieldGroup('C', inline: true)->render(new RenderContext(), 'x'),
        );
    }

    public function testACheckboxInAGroupCarriesItsOwnLabelAndListItem(): void
    {
        self::assertSame(
            '<li><div class="rvt-checkbox">'
            . '<input type="checkbox" id="rvt-checkbox-1" name="campus" value="bl">'
            . '<label for="rvt-checkbox-1">Bloomington</label>'
            . '</div></li>',
            $this->inGroup(new Checkbox(name: 'campus', value: 'bl', label: 'Bloomington')),
            'Each choice has its own label, unlike a text input whose label the field owns.',
        );
    }

    public function testEachChoiceGetsADistinctId(): void
    {
        $context = new RenderContext();
        $context->open(new FieldGroup('C'));

        $first = new Checkbox(name: 'c', value: '1', label: 'One')->render($context);
        $second = new Checkbox(name: 'c', value: '2', label: 'Two')->render($context);

        self::assertStringContainsString('id="rvt-checkbox-1"', $first);
        self::assertStringContainsString(
            'id="rvt-checkbox-2"',
            $second,
            'Choices are nearly always rendered in a loop, which is exactly where hand-written ids collide.',
        );
    }

    public function testACheckedAndDisabledChoiceCarriesBothAttributes(): void
    {
        $html = $this->inGroup(new Checkbox(name: 'c', value: '1', label: 'One', checked: true, disabled: true));

        self::assertStringContainsString('checked', $html);
        self::assertStringContainsString('disabled', $html);
    }

    public function testADescriptionIsLinkedToTheChoiceThatOwnsIt(): void
    {
        $html = $this->inGroup(new Checkbox(
            name: 'c',
            value: '1',
            label: 'Email me',
            description: 'Occasional updates only.',
        ));

        self::assertStringContainsString('aria-describedby="rvt-checkbox-1-description"', $html);
        self::assertStringContainsString(
            '<div class="rvt-checkbox__description" id="rvt-checkbox-1-description">Occasional updates only.</div>',
            $html,
        );
    }

    public function testAVisuallyHiddenChoiceLabelUsesTheModifier(): void
    {
        self::assertStringContainsString(
            'class="rvt-checkbox rvt-checkbox--sr-only-label"',
            $this->inGroup(new Checkbox(name: 'c', value: '1', label: 'Select row', labelHidden: true)),
        );
    }

    public function testAStandaloneCheckboxOmitsTheListItem(): void
    {
        self::assertStringStartsWith(
            '<div class="rvt-checkbox">',
            new Checkbox(name: 'agree', value: 'yes', label: 'I agree')->render(new RenderContext()),
            'A single checkbox outside a group is legitimate and needs no list around it.',
        );
    }

    public function testARadioOutsideAGroupIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_radio must be used inside rvt_field_group.');

        new Radio(name: 'c', value: '1', label: 'One')->render(new RenderContext());
    }

    public function testARadioUsesItsOwnBlockClass(): void
    {
        self::assertStringContainsString(
            '<div class="rvt-radio">',
            $this->inGroup(new Radio(name: 'c', value: '1', label: 'One')),
            'Radios and checkboxes differ only in the block class and the input type.',
        );
    }

    public function testChoicesInAGroupWithErrorsReferenceTheGroupMessage(): void
    {
        $context = new RenderContext();
        $context->open(new FieldGroup('C', errors: ['Pick one.']));

        self::assertStringContainsString(
            'aria-describedby="rvt-field-group-1-error-1"',
            new Radio(name: 'c', value: '1', label: 'One')->render($context),
            'A validation message on the group describes every choice inside it.',
        );
    }

    private function inGroup(Checkbox|Radio $choice): string
    {
        $context = new RenderContext();
        $context->open(new FieldGroup('Campus'));

        return $choice->render($context);
    }
}
