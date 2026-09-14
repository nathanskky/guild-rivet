<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Render;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Component\Form\FormField;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\ComponentFactory;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentFactory::class)]
final class ComponentFactoryTest extends TestCase
{
    public function testNamedArgumentsArePassedToTheConstructor(): void
    {
        $badge = new ComponentFactory()->create(Badge::class, ['text' => 'New']);

        self::assertStringContainsString(
            '>New<',
            $badge->render(new RenderContext()),
            'Template arguments map onto constructor parameters by name.',
        );
    }

    public function testAStringIsCoercedIntoABackedEnum(): void
    {
        $alert = new ComponentFactory()->create(Alert::class, ['title' => 'T', 'style' => 'danger']);

        self::assertStringContainsString(
            'rvt-alert--danger',
            $alert->render(new RenderContext()),
            'Templates cannot write PHP enum cases, so a string must resolve to one.',
        );
    }

    public function testAnEnumInstanceIsAcceptedUnchanged(): void
    {
        $alert = new ComponentFactory()->create(Alert::class, ['title' => 'T', 'style' => AlertStyle::Warning]);

        self::assertStringContainsString(
            'rvt-alert--warning',
            $alert->render(new RenderContext()),
            'Calling from PHP should let the caller pass the enum directly.',
        );
    }

    public function testSnakeCaseArgumentsReachCamelCaseConstructorParameters(): void
    {
        $field = new ComponentFactory()->create(FormField::class, [
            'label' => 'Email',
            'helper_text' => 'We only use this to contact you.',
        ]);

        self::assertStringContainsString(
            '<div class="rvt-ts-14 rvt-color-black-500 rvt-m-top-xxs" id="rvt-field-1-helper">We only use this to contact you.</div>',
            $field->render(new RenderContext(), '<input>'),
            'Templates write snake_case because Twig cannot lex a hyphen, so it must reach a camelCase parameter rather than silently becoming an attribute on the label.',
        );
    }

    public function testACamelCaseArgumentStillWorksWhenCallingFromPhp(): void
    {
        $field = new ComponentFactory()->create(FormField::class, ['label' => 'E', 'helperText' => 'Hint.']);

        self::assertStringContainsString('id="rvt-field-1-helper">Hint.</div>', $field->render(new RenderContext(), '<input>'));
    }

    public function testAnUnmatchedSnakeCaseArgumentIsStillAnAttribute(): void
    {
        $badge = new ComponentFactory()->create(Badge::class, ['text' => 'x', 'data_testid' => 'b']);

        self::assertStringContainsString(
            'data-testid="b"',
            $badge->render(new RenderContext()),
            'Only names matching a parameter are consumed; the rest remain HTML attributes.',
        );
    }

    public function testUnknownArgumentsAreCollectedAsExtraAttributes(): void
    {
        $badge = new ComponentFactory()->create(Badge::class, ['text' => 'x', 'data_testid' => 'b', 'class' => 'rvt-m-top-md']);

        $html = $badge->render(new RenderContext());

        self::assertStringContainsString('data-testid="b"', $html, 'Anything not a constructor parameter becomes an HTML attribute.');
        self::assertStringContainsString('class="rvt-badge rvt-m-top-md"', $html, 'Caller classes still merge rather than replace.');
    }

    public function testAnInvalidEnumValueNamesTheAllowedOnes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"nope" is not a valid style for rvt_alert. Expected one of: info, success, warning, danger.');

        new ComponentFactory()->create(Alert::class, ['title' => 'T', 'style' => 'nope']);
    }

    public function testPositionalArgumentsMapToConstructorParametersInOrder(): void
    {
        $badge = new ComponentFactory()->create(Badge::class, ['New', 'style' => 'info']);

        self::assertSame(
            '<span class="rvt-badge rvt-badge--info">New</span>',
            $badge->render(new RenderContext()),
            'Templates should be able to pass the obvious first argument without naming it.',
        );
    }

    public function testAPositionalArgumentThatAlsoHasANameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_badge received the "text" argument both by position and by name.');

        new ComponentFactory()->create(Badge::class, ['New', 'text' => 'Other']);
    }

    public function testTooManyPositionalArgumentsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_badge accepts 4 positional arguments, 5 given.');

        new ComponentFactory()->create(Badge::class, ['a', 'b', 'c', 'd', 'e']);
    }

    public function testAMissingRequiredArgumentIsReportedByName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_badge requires the "text" argument.');

        new ComponentFactory()->create(Badge::class, []);
    }
}
