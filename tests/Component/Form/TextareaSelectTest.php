<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Form;

use Guild\Rivet\Component\Form\FormField;
use Guild\Rivet\Component\Form\Select;
use Guild\Rivet\Component\Form\Textarea;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Textarea::class)]
#[CoversClass(Select::class)]
final class TextareaSelectTest extends TestCase
{
    public function testATextareaCarriesItsValueAsTextContent(): void
    {
        self::assertSame(
            '<textarea class="rvt-textarea" id="rvt-field-1" name="bio">A &amp; B</textarea>',
            $this->inField(new Textarea(name: 'bio', value: 'A & B')),
            'A textarea holds its value between the tags, and it is caller data.',
        );
    }

    public function testATextareaRowsAreOptional(): void
    {
        self::assertStringContainsString(
            'rows="8"',
            $this->inField(new Textarea(name: 'bio', rows: 8)),
        );
    }

    public function testATextareaOutsideAFormFieldIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);

        new Textarea(name: 'x')->render(new RenderContext());
    }

    public function testASelectRendersItsOptions(): void
    {
        self::assertSame(
            '<select class="rvt-select" id="rvt-field-1" name="campus">'
            . '<option value="bl">Bloomington</option>'
            . '<option value="in" selected>Indianapolis</option>'
            . '</select>',
            $this->inField(new Select(name: 'campus', options: [
                ['value' => 'bl', 'label' => 'Bloomington'],
                ['value' => 'in', 'label' => 'Indianapolis', 'selected' => true],
            ])),
            'Options are ordinary data, so describing them as an array beats nesting a component per option.',
        );
    }

    public function testASelectCanTakeAPlaceholderOption(): void
    {
        self::assertStringContainsString(
            '<option value="" disabled selected>Choose a campus</option>',
            $this->inField(new Select(name: 'c', placeholder: 'Choose a campus', options: [
                ['value' => 'bl', 'label' => 'Bloomington'],
            ])),
            'A disabled, selected, empty option is how a select shows a prompt without it being choosable.',
        );
    }

    public function testAnOptionLabelIsEscaped(): void
    {
        self::assertStringContainsString(
            '>Arts &amp; Sciences<',
            $this->inField(new Select(name: 'c', options: [['value' => 'a', 'label' => 'Arts & Sciences']])),
        );
    }

    public function testASelectInsideAFieldWithErrorsIsMarkedInvalid(): void
    {
        $html = $this->inField(
            new Select(name: 'c', options: [['value' => 'a', 'label' => 'A']]),
            new FormField('Campus', errors: ['Pick one.']),
        );

        self::assertStringContainsString('rvt-validation-danger', $html);
        self::assertStringContainsString('aria-invalid="true"', $html);
    }

    private function inField(Select|Textarea $control, ?FormField $field = null): string
    {
        $context = new RenderContext();
        $context->open($field ?? new FormField('Label'));

        return $control->render($context);
    }
}
