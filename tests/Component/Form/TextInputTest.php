<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Form;

use Guild\Rivet\Component\Form\FormField;
use Guild\Rivet\Component\Form\TextInput;
use Guild\Rivet\Enum\InputType;
use Guild\Rivet\Enum\ValidationState;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextInput::class)]
final class TextInputTest extends TestCase
{
    public function testTheInputTakesItsIdFromTheFieldThatLabelsIt(): void
    {
        self::assertSame(
            '<input class="rvt-text-input" type="text" id="rvt-field-1" name="username">',
            $this->inField(new TextInput(name: 'username')),
            'The id must match the label for attribute, which the field owns.',
        );
    }

    public function testAnInputOutsideAFormFieldIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_text_input must be used inside rvt_form_field.');

        new TextInput(name: 'x')->render(new RenderContext());
    }

    public function testARequiredFieldMakesItsInputRequired(): void
    {
        $html = $this->inField(new TextInput(name: 'x'), new FormField('U', required: true));

        self::assertStringContainsString('required', $html);
        self::assertStringContainsString('aria-required="true"', $html, 'Some assistive technology reads the ARIA state rather than the attribute.');
    }

    public function testHelperTextAndErrorsBecomeTheDescribedByValue(): void
    {
        self::assertStringContainsString(
            'aria-describedby="rvt-field-1-helper rvt-field-1-error-1"',
            $this->inField(
                new TextInput(name: 'x'),
                new FormField('U', helperText: 'Hint.', errors: ['Taken.']),
            ),
            'The control must reference every section describing it, and only those.',
        );
    }

    public function testAFieldWithErrorsMarksItsInputInvalidAndStyled(): void
    {
        $html = $this->inField(new TextInput(name: 'x'), new FormField('U', errors: ['Taken.']));

        self::assertStringContainsString('class="rvt-text-input rvt-validation-danger"', $html);
        self::assertStringContainsString('aria-invalid="true"', $html);
    }

    public function testAValidationStateCanBeSetWithoutAnErrorMessage(): void
    {
        self::assertStringContainsString(
            'rvt-validation-success',
            $this->inField(new TextInput(name: 'x', validation: ValidationState::Success)),
            'A field can be marked valid without anything to say about it.',
        );
    }

    public function testTheInputTypeIsCarriedThrough(): void
    {
        self::assertStringContainsString(
            'type="email"',
            $this->inField(new TextInput(name: 'x', type: InputType::Email)),
        );
    }

    public function testValuePlaceholderAndStateAttributesAreEmitted(): void
    {
        $html = $this->inField(new TextInput(
            name: 'x',
            value: 'a & b',
            placeholder: 'you@iu.edu',
            readonly: true,
            disabled: true,
        ));

        self::assertStringContainsString('value="a &amp; b"', $html, 'A submitted value is caller data and must be escaped.');
        self::assertStringContainsString('placeholder="you@iu.edu"', $html);
        self::assertStringContainsString('readonly', $html);
        self::assertStringContainsString('disabled', $html);
    }

    private function inField(TextInput $input, ?FormField $field = null): string
    {
        $context = new RenderContext();
        $context->open($field ?? new FormField('Username'));

        return $input->render($context);
    }
}
