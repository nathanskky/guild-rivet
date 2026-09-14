<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Button;
use Guild\Rivet\Enum\ButtonFill;
use Guild\Rivet\Enum\ButtonPurpose;
use Guild\Rivet\Enum\ButtonSize;
use Guild\Rivet\Enum\ButtonType;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Button::class)]
final class ButtonTest extends TestCase
{
    public function testADefaultButtonIsTheUnmodifiedBlock(): void
    {
        self::assertSame(
            '<button class="rvt-button" type="button">Save</button>',
            new Button('Save')->render(new RenderContext()),
            'The default primary button carries no modifier.',
        );
    }

    public function testTextIsEscapedButAContentBodyIsNot(): void
    {
        self::assertSame(
            '<button class="rvt-button" type="button">Fish &amp; Chips</button>',
            new Button('Fish & Chips')->render(new RenderContext()),
            'The text argument is caller data.',
        );

        self::assertSame(
            '<button class="rvt-button" type="button"><svg></svg><span>Add</span></button>',
            new Button()->render(new RenderContext(), '<svg></svg><span>Add</span>'),
            'A body lets a caller combine an icon with a label.',
        );
    }

    public function testABodyTakesPrecedenceOverTheTextArgument(): void
    {
        self::assertStringContainsString(
            '>Body<',
            new Button('Text')->render(new RenderContext(), 'Body'),
            'Writing content between the tags is the more specific instruction.',
        );
    }

    #[DataProvider('modifierCombinations')]
    public function testPurposeAndFillComposeIntoOneModifier(
        ButtonPurpose $purpose,
        ButtonFill $fill,
        string $expected,
    ): void {
        self::assertSame(
            sprintf('<button class="%s" type="button">x</button>', $expected),
            new Button('x', purpose: $purpose, fill: $fill)->render(new RenderContext()),
            'Purpose and fill are independent axes resolved to one Rivet class.',
        );
    }

    /**
     * @return iterable<string, array{ButtonPurpose, ButtonFill, string}>
     */
    public static function modifierCombinations(): iterable
    {
        yield 'default solid' => [ButtonPurpose::Default, ButtonFill::Solid, 'rvt-button'];
        yield 'default outline' => [ButtonPurpose::Default, ButtonFill::Outline, 'rvt-button rvt-button--secondary'];
        yield 'success solid' => [ButtonPurpose::Success, ButtonFill::Solid, 'rvt-button rvt-button--success'];
        yield 'success outline' => [ButtonPurpose::Success, ButtonFill::Outline, 'rvt-button rvt-button--success-secondary'];
        yield 'danger outline' => [ButtonPurpose::Danger, ButtonFill::Outline, 'rvt-button rvt-button--danger-secondary'];
        yield 'plain solid' => [ButtonPurpose::Plain, ButtonFill::Solid, 'rvt-button rvt-button--plain'];
    }

    public function testPlainHasNoOutlineVariantAndSaysSo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rivet has no outline variant for a plain button.');

        new Button('x', purpose: ButtonPurpose::Plain, fill: ButtonFill::Outline)->render(new RenderContext());
    }

    public function testSizeAndFullWidthAreAppendedAfterThePurposeModifier(): void
    {
        self::assertStringContainsString(
            'class="rvt-button rvt-button--danger rvt-button--small rvt-button--full-width"',
            new Button('x', purpose: ButtonPurpose::Danger, size: ButtonSize::Small, fullWidth: true)
                ->render(new RenderContext()),
            'Size and width are further independent axes, appended in Rivet document order.',
        );
    }

    public function testTheTypeAttributeIsAlwaysExplicit(): void
    {
        self::assertStringContainsString(
            'type="submit"',
            new Button('Go', type: ButtonType::Submit)->render(new RenderContext()),
            'Omitting type makes a button submit its form by accident; Rivet examples always state it.',
        );
    }

    public function testADisabledButtonCarriesTheBooleanAttribute(): void
    {
        self::assertStringContainsString(
            '<button class="rvt-button" type="button" disabled>',
            new Button('x', disabled: true)->render(new RenderContext()),
            'disabled is a valueless boolean attribute.',
        );
    }

    public function testAButtonWithNoAccessibleNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_button needs either text or content to give it an accessible name.');

        new Button()->render(new RenderContext());
    }
}
