<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Html;

use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attributes::class)]
final class AttributesTest extends TestCase
{
    public function testAnEmptySetHasNoAttributesAndNoClasses(): void
    {
        $attributes = new Attributes();

        self::assertSame([], $attributes->all(), 'A default Attributes carries nothing.');
        self::assertSame([], $attributes->classes(), 'A default Attributes contributes no classes.');
    }

    public function testUnderscoresInKeysBecomeHyphens(): void
    {
        $attributes = new Attributes(['data_rvt_dialog' => 'confirm']);

        self::assertSame(
            ['data-rvt-dialog' => 'confirm'],
            $attributes->all(),
            'Twig cannot lex hyphens in tag syntax, so snake_case keys are the portable spelling and map to hyphens here.',
        );
    }

    public function testAlreadyHyphenatedKeysArePreserved(): void
    {
        $attributes = new Attributes(['aria-label' => 'Close']);

        self::assertSame(
            ['aria-label' => 'Close'],
            $attributes->all(),
            'Calling from plain PHP, where hyphens are legal, must work too.',
        );
    }

    public function testClassIsSeparatedFromTheOtherAttributes(): void
    {
        $attributes = new Attributes(['class' => 'rvt-m-top-md rvt-text-bold', 'id' => 'x']);

        self::assertSame(
            ['rvt-m-top-md', 'rvt-text-bold'],
            $attributes->classes(),
            'class is split into names so it can be merged with component classes rather than overwrite them.',
        );
        self::assertSame(
            ['id' => 'x'],
            $attributes->all(),
            'class must not also appear among the plain attributes, or it would be emitted twice.',
        );
    }

    #[DataProvider('invalidAttributeNames')]
    public function testInvalidAttributeNamesAreRejected(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Attributes([$name => 'x']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidAttributeNames(): iterable
    {
        yield 'empty' => [''];
        yield 'contains a space' => ['on click'];
        yield 'contains a quote' => ['on"click'];
        yield 'contains an angle bracket' => ['a<b'];
        yield 'starts with a digit' => ['1abc'];
    }
}
