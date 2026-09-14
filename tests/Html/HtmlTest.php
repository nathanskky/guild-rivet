<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Html;

use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Html::class)]
final class HtmlTest extends TestCase
{
    public function testAnElementWithNoAttributesOrChildrenRendersAsAnEmptyTagPair(): void
    {
        self::assertSame(
            '<div></div>',
            Html::el('div')->render(),
            'A bare element should render as an open tag immediately followed by its closing tag.',
        );
    }

    public function testTextContentIsRenderedInsideTheElement(): void
    {
        self::assertSame(
            '<p>Hello</p>',
            Html::el('p')->text('Hello')->render(),
            'text() should place its value between the open and close tags.',
        );
    }

    public function testTextContentIsHtmlEscaped(): void
    {
        self::assertSame(
            '<p>Fish &amp; Chips &lt;script&gt;</p>',
            Html::el('p')->text('Fish & Chips <script>')->render(),
            'text() must escape HTML special characters so caller data cannot inject markup.',
        );
    }

    public function testHtmlContentIsNotEscaped(): void
    {
        self::assertSame(
            '<p><strong>bold</strong></p>',
            Html::el('p')->html('<strong>bold</strong>')->render(),
            'html() is the explicit opt-out from escaping, for already-rendered markup.',
        );
    }

    public function testAttributesAreRenderedInTheOpeningTag(): void
    {
        self::assertSame(
            '<div id="main"></div>',
            Html::el('div')->attr('id', 'main')->render(),
            'attr() should emit name="value" inside the opening tag.',
        );
    }

    public function testAttributesRenderInTheOrderTheyWereAdded(): void
    {
        self::assertSame(
            '<div id="a" role="status"></div>',
            Html::el('div')->attr('id', 'a')->attr('role', 'status')->render(),
            'Attribute order must be deterministic so golden-file fixtures are stable.',
        );
    }

    public function testAttributeValuesAreEscaped(): void
    {
        self::assertSame(
            '<div title="5 &gt; 3 &amp; &quot;quoted&quot;"></div>',
            Html::el('div')->attr('title', '5 > 3 & "quoted"')->render(),
            'Attribute values must be escaped, including quotes, to prevent attribute injection.',
        );
    }

    public function testTrueRendersABareBooleanAttribute(): void
    {
        self::assertSame(
            '<button data-rvt-alert-close></button>',
            Html::el('button')->attr('data-rvt-alert-close', true)->render(),
            'Rivet uses valueless boolean attributes; true must render the name alone.',
        );
    }

    #[DataProvider('omittedAttributeValues')]
    public function testAttributesWithNullOrFalseValuesAreOmittedEntirely(bool|string|null $value): void
    {
        self::assertSame(
            '<div></div>',
            Html::el('div')->attr('hidden', $value)->render(),
            'null and false must drop the attribute so conditionals stay inline at call sites.',
        );
    }

    /**
     * @return iterable<string, array{bool|string|null}>
     */
    public static function omittedAttributeValues(): iterable
    {
        yield 'null' => [null];
        yield 'false' => [false];
    }

    public function testClassNamesAreRenderedAsASingleClassAttribute(): void
    {
        self::assertSame(
            '<div class="rvt-alert rvt-alert--info"></div>',
            Html::el('div')->class('rvt-alert', 'rvt-alert--info')->render(),
            'class() takes varargs and joins them with a single space.',
        );
    }

    public function testClassNamesAccumulateAcrossCalls(): void
    {
        self::assertSame(
            '<div class="rvt-card rvt-card--raised"></div>',
            Html::el('div')->class('rvt-card')->class('rvt-card--raised')->render(),
            'Later class() calls append, so a component can add modifiers conditionally.',
        );
    }

    public function testDuplicateClassNamesAreCollapsed(): void
    {
        self::assertSame(
            '<div class="rvt-button rvt-button--small"></div>',
            Html::el('div')->class('rvt-button', 'rvt-button--small')->class('rvt-button')->render(),
            'A class repeated by a caller must not be emitted twice.',
        );
    }

    public function testNullAndEmptyClassNamesAreIgnored(): void
    {
        self::assertSame(
            '<div class="rvt-card"></div>',
            Html::el('div')->class('rvt-card', null, '')->render(),
            'A conditional modifier that evaluates to null should simply not appear.',
        );
    }

    public function testAnElementWithNoClassesHasNoClassAttribute(): void
    {
        self::assertSame(
            '<div></div>',
            Html::el('div')->class(null)->render(),
            'An empty class list must omit the attribute rather than emit class="".',
        );
    }

    public function testClassKeepsThePositionOfItsFirstCall(): void
    {
        self::assertSame(
            '<div class="rvt-alert" id="a"></div>',
            Html::el('div')->class('rvt-alert')->attr('id', 'a')->class('rvt-alert')->render(),
            'Class position is fixed at first use so later modifiers do not reorder output.',
        );
    }

    public function testChildElementsAreRenderedInsideTheParent(): void
    {
        self::assertSame(
            '<div class="rvt-card"><p>Body</p></div>',
            Html::el('div')->class('rvt-card')->children(Html::el('p')->text('Body'))->render(),
            'children() should render each child element in order inside the parent.',
        );
    }

    public function testNullChildrenAreDropped(): void
    {
        self::assertSame(
            '<div><p>Kept</p></div>',
            Html::el('div')->children(Html::el('p')->text('Kept'), null)->render(),
            'A null child must vanish, so an optional element stays a one-line conditional.',
        );
    }

    public function testChildrenAppendAfterEarlierTextContent(): void
    {
        self::assertSame(
            '<div>Label<span>x</span></div>',
            Html::el('div')->text('Label')->children(Html::el('span')->text('x'))->render(),
            'Content is appended in call order regardless of which method added it.',
        );
    }

    public function testVoidElementsRenderWithoutAClosingTag(): void
    {
        self::assertSame(
            '<input type="text" class="rvt-text-input">',
            Html::el('input')->attr('type', 'text')->class('rvt-text-input')->render(),
            'Void elements such as input must not emit a closing tag.',
        );
    }

    public function testAddingContentToAVoidElementThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Void element "input" cannot have content.');

        Html::el('input')->text('nope');
    }

    public function testMergeAppendsCallerClassesAfterComponentClasses(): void
    {
        self::assertSame(
            '<div class="rvt-alert rvt-alert--info rvt-m-top-md"></div>',
            Html::el('div')
                ->class('rvt-alert', 'rvt-alert--info')
                ->merge(new Attributes(['class' => 'rvt-m-top-md']))
                ->render(),
            'Caller utility classes are appended, never substituted for the component classes.',
        );
    }

    public function testMergeAddsCallerAttributesAfterComponentAttributes(): void
    {
        self::assertSame(
            '<div id="a" data-testid="alert"></div>',
            Html::el('div')
                ->attr('id', 'a')
                ->merge(new Attributes(['data_testid' => 'alert']))
                ->render(),
            'Extra attributes render after the component attributes, keeping output order stable.',
        );
    }

    public function testMergeDoesNotOverrideAnAttributeTheComponentAlreadySet(): void
    {
        self::assertSame(
            '<div role="alert"></div>',
            Html::el('div')
                ->attr('role', 'alert')
                ->merge(new Attributes(['role' => 'presentation']))
                ->render(),
            'The component wins on conflict, so a caller cannot accidentally break the ARIA wiring the component is responsible for.',
        );
    }
}
