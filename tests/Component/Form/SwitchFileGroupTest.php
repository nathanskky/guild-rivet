<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Form;

use Guild\Rivet\Component\Form\FileInput;
use Guild\Rivet\Component\Form\FormField;
use Guild\Rivet\Component\Form\InputGroup;
use Guild\Rivet\Component\Form\InputGroupAddon;
use Guild\Rivet\Component\Form\TextInput;
use Guild\Rivet\Component\Form\ToggleSwitch;
use Guild\Rivet\Enum\AddonPosition;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToggleSwitch::class)]
#[CoversClass(FileInput::class)]
#[CoversClass(InputGroup::class)]
#[CoversClass(InputGroupAddon::class)]
final class SwitchFileGroupTest extends TestCase
{
    public function testASwitchIsAButtonWithASwitchRoleAndAName(): void
    {
        self::assertSame(
            '<button class="rvt-switch" type="button" data-rvt-switch="rvt-switch-1" role="switch" aria-label="Two-factor authentication">'
            . '<span class="rvt-switch__on">On</span>'
            . '<span class="rvt-switch__off">Off</span>'
            . '</button>',
            new ToggleSwitch(label: 'Two-factor authentication')->render(new RenderContext()),
            'Rivet builds a switch from a button; role and label are required because the on/off spans are visual only.',
        );
    }

    public function testASwitchWithoutALabelIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_switch requires a label.');

        new ToggleSwitch(label: '')->render(new RenderContext());
    }

    public function testASwitchThatStartsOnSaysSo(): void
    {
        self::assertStringContainsString(
            'data-rvt-switch-on',
            new ToggleSwitch(label: 'x', on: true)->render(new RenderContext()),
            'Rivet reads the initial state from a valueless attribute, not from aria-checked, which its JavaScript owns.',
        );
    }

    public function testAFileInputRepeatsOneIdentifierAcrossEveryPart(): void
    {
        self::assertSame(
            '<div class="rvt-file" data-rvt-file-input="rvt-file-1">'
            . '<input type="file" id="rvt-file-1" name="upload" data-rvt-file-input-button="rvt-file-1" aria-describedby="rvt-file-1-description">'
            . '<label class="rvt-button" for="rvt-file-1"><span>Upload a file</span>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">'
            . '<path d="M2 1h8.414L14 4.586V15H2V1Zm2 2v10h8V7.5H7.5V3H4Zm5.5 0v2.5H12v-.086L9.586 3H9.5Z"/></svg></label>'
            . '<div class="rvt-file__preview" id="rvt-file-1-description" data-rvt-file-input-preview="rvt-file-1">No file selected</div>'
            . '</div>',
            new FileInput(name: 'upload')->render(new RenderContext()),
            'The same identifier appears in five places; Rivet JavaScript writes the chosen filename into the preview it names.',
        );
    }

    public function testAFileInputCanAcceptSeveralFiles(): void
    {
        self::assertStringContainsString(
            'multiple',
            new FileInput(name: 'u', multiple: true)->render(new RenderContext()),
        );
    }

    public function testAnInputGroupMarksTheInputInsideIt(): void
    {
        self::assertStringContainsString(
            'class="rvt-text-input rvt-input-group__input"',
            $this->inGroup('<INPUT>'),
            'The input takes an extra class inside a group, which it discovers rather than being told twice.',
        );
    }

    public function testAnAddonRendersOnTheSideItWasGiven(): void
    {
        $context = new RenderContext();
        $context->open(new FormField('Email'));
        $context->open(new InputGroup());

        self::assertSame(
            '<div class="rvt-input-group__append"><div class="rvt-input-group__text">@iu.edu</div></div>',
            new InputGroupAddon(text: '@iu.edu')->render($context),
            'A text addon is wrapped so Rivet can style it against the input.',
        );
    }

    public function testAPrependedAddonUsesTheOtherWrapper(): void
    {
        $context = new RenderContext();
        $context->open(new FormField('Amount'));
        $context->open(new InputGroup());

        self::assertStringContainsString(
            '<div class="rvt-input-group__prepend">',
            new InputGroupAddon(text: '$', position: AddonPosition::Prepend)->render($context),
        );
    }

    public function testAnAddonCanHoldArbitraryContentSuchAsAButton(): void
    {
        $context = new RenderContext();
        $context->open(new FormField('Search'));
        $context->open(new InputGroup());

        self::assertSame(
            '<div class="rvt-input-group__append"><button class="rvt-button">Go</button></div>',
            new InputGroupAddon()->render($context, '<button class="rvt-button">Go</button>'),
            'The search pattern in Rivet appends a button rather than text.',
        );
    }

    public function testAnAddonOutsideAnInputGroupIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_input_group_addon must be used inside rvt_input_group.');

        new InputGroupAddon(text: 'x')->render(new RenderContext());
    }

    private function inGroup(string $unused): string
    {
        $context = new RenderContext();
        $context->open(new FormField('Email'));
        $context->open(new InputGroup());

        return new TextInput(name: 'email')->render($context);
    }
}
