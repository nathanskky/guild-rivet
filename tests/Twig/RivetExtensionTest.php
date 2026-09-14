<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Twig;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Component\Button;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Twig\RivetExtension;
use Guild\Rivet\Twig\RivetRuntime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

#[CoversClass(RivetExtension::class)]
#[CoversClass(RivetRuntime::class)]
final class RivetExtensionTest extends TestCase
{
    public function testALeafComponentRendersThroughAFunction(): void
    {
        self::assertSame(
            '<span class="rvt-badge rvt-badge--info">New</span>',
            $this->render("{{ rvt_badge(text: 'New', style: 'info') }}"),
            'Leaf components read most naturally as Twig functions.',
        );
    }

    public function testTheFirstArgumentMayBePassedByPosition(): void
    {
        self::assertSame(
            '<span class="rvt-badge rvt-badge--danger">Overdue</span>',
            $this->render("{{ rvt_badge('Overdue', style: 'danger') }}"),
            'Mixing one positional argument with named ones is the most natural way to write this.',
        );
    }

    public function testFunctionOutputIsNotDoubleEscaped(): void
    {
        self::assertStringNotContainsString(
            '&lt;span',
            $this->render("{{ rvt_badge(text: 'New') }}"),
            'The function is declared html-safe, so Twig must not escape the markup it returns.',
        );
    }

    public function testABlockComponentAlsoHasAShortFunctionForm(): void
    {
        self::assertSame(
            '<button class="rvt-button rvt-button--danger" type="button">Delete</button>',
            $this->render("{{ rvt_button('Delete', purpose: 'danger') }}"),
            'A button whose body is just a label should not need a closing tag.',
        );
    }

    public function testABlockComponentCapturesItsBody(): void
    {
        self::assertSame(
            '<div class="rvt-alert rvt-alert--info" role="alert" aria-labelledby="rvt-alert-1-title" data-rvt-alert="rvt-alert-1">'
            . '<div class="rvt-alert__title" id="rvt-alert-1-title">Notice</div>'
            . '<div class="rvt-alert__message"><p>Body</p></div>'
            . '</div>',
            $this->render("{% rvt_alert title='Notice' dismissible=false %}<p>Body</p>{% endrvt_alert %}"),
            'A paired tag renders its component around the markup between the tags.',
        );
    }

    public function testSurroundingWhitespaceInTheBodyIsTrimmed(): void
    {
        self::assertStringContainsString(
            '<div class="rvt-alert__message"><p>Body</p></div>',
            $this->render("{% rvt_alert title='N' dismissible=false %}\n    <p>Body</p>\n{% endrvt_alert %}"),
            'Indented templates must not change the emitted markup.',
        );
    }

    public function testTemplateVariablesCanBePassedAsArguments(): void
    {
        self::assertStringContainsString(
            '>Fish &amp; Chips<',
            $this->render('{{ rvt_badge(text: label) }}', ['label' => 'Fish & Chips']),
            'Values from the template context flow through and are still escaped by the component.',
        );
    }

    public function testATagWithNoArgumentsParses(): void
    {
        self::assertStringContainsString(
            'rvt-alert',
            $this->render("{% rvt_alert title='x' %}b{% endrvt_alert %}"),
            'Argument parsing must terminate cleanly at the block end.',
        );
    }

    public function testNestedBlockComponentsEachGetTheirOwnId(): void
    {
        $html = $this->render(
            "{% rvt_alert title='Outer' dismissible=false %}{% rvt_alert title='Inner' dismissible=false %}x{% endrvt_alert %}{% endrvt_alert %}"
        );

        self::assertStringContainsString('id="rvt-alert-1-title"', $html, 'The outer alert opens first and takes the first id.');
        self::assertStringContainsString('id="rvt-alert-2-title"', $html, 'A nested alert must not reuse its parent id.');
    }

    public function testAnUnknownArgumentBecomesAnHtmlAttribute(): void
    {
        self::assertStringContainsString(
            'data-testid="b"',
            $this->render("{{ rvt_badge(text: 'x', data_testid: 'b') }}"),
            'snake_case keys map to hyphens, which is the only spelling Twig can lex in tag syntax.',
        );
    }

    public function testAnUnknownComponentTagIsASyntaxError(): void
    {
        $this->expectException(SyntaxError::class);

        $this->render('{% rvt_nope %}x{% endrvt_nope %}');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function render(string $template, array $context = []): string
    {
        $registry = new ComponentRegistry([Badge::class, Button::class, Alert::class]);
        $renderer = new Renderer($registry);

        $twig = new Environment(new ArrayLoader(['t' => $template]));
        $twig->addExtension(new RivetExtension($registry));
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
        ]));

        return $twig->render('t', $context);
    }
}
