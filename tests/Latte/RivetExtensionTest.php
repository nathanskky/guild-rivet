<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Latte;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Component\Button;
use Guild\Rivet\Latte\RivetExtension;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\Renderer;
use Latte\CompileException;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RivetExtension::class)]
final class RivetExtensionTest extends TestCase
{
    public function testALeafComponentRendersFromACamelCaseTag(): void
    {
        self::assertSame(
            '<span class="rvt-badge rvt-badge--info">New</span>',
            $this->render("{rvtBadge 'New', style: 'info'}"),
            'Latte tags are camelCase, which is what reads as native there.',
        );
    }

    public function testMarkupIsNotEscapedByLatte(): void
    {
        self::assertStringNotContainsString(
            '&lt;span',
            $this->render("{rvtBadge 'New'}"),
            "Latte's context-aware escaper must not touch markup the component already produced.",
        );
    }

    public function testABlockComponentAlsoHasAShortVoidTagForm(): void
    {
        self::assertSame(
            '<button class="rvt-button rvt-button--danger" type="button">Delete</button>',
            $this->render("{rvtButton 'Delete', purpose: 'danger' /}"),
            "Latte's self-closing tag syntax gives the same short form as Twig's function.",
        );
    }

    public function testABlockComponentCapturesItsBody(): void
    {
        self::assertSame(
            '<div class="rvt-alert rvt-alert--info" role="alert" aria-labelledby="rvt-alert-1-title" data-rvt-alert="rvt-alert-1">'
            . '<div class="rvt-alert__title" id="rvt-alert-1-title">Notice</div>'
            . '<div class="rvt-alert__message"><p>Body</p></div>'
            . '</div>',
            $this->render("{rvtAlert title: 'Notice', dismissible: false}<p>Body</p>{/rvtAlert}"),
            'A paired tag renders its component around the captured body.',
        );
    }

    public function testSurroundingWhitespaceInTheBodyIsTrimmed(): void
    {
        self::assertStringContainsString(
            '<div class="rvt-alert__message"><p>Body</p></div>',
            $this->render("{rvtAlert title: 'N', dismissible: false}\n    <p>Body</p>\n{/rvtAlert}"),
            'Latte moves leading indentation into the following element, so the body must be trimmed.',
        );
    }

    public function testTemplateVariablesCanBePassedAsArguments(): void
    {
        self::assertStringContainsString(
            '>Fish &amp; Chips<',
            $this->render('{rvtBadge $label}', ['label' => 'Fish & Chips']),
            'Values from the template flow through and are escaped by the component.',
        );
    }

    public function testNestedBlockComponentsEachGetTheirOwnId(): void
    {
        $html = $this->render(
            "{rvtAlert title: 'Outer', dismissible: false}{rvtAlert title: 'Inner', dismissible: false}x{/rvtAlert}{/rvtAlert}"
        );

        self::assertStringContainsString('id="rvt-alert-1-title"', $html);
        self::assertStringContainsString('id="rvt-alert-2-title"', $html, 'A nested alert must not reuse its parent id.');
    }

    public function testAnUnknownArgumentBecomesAnHtmlAttribute(): void
    {
        self::assertStringContainsString(
            'data-testid="b"',
            $this->render("{rvtBadge 'x', data_testid: 'b'}"),
            'The same snake_case spelling works in both engines.',
        );
    }

    public function testAnUnknownComponentTagIsACompileError(): void
    {
        $this->expectException(CompileException::class);

        $this->render('{rvtNope}');
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function render(string $template, array $parameters = []): string
    {
        $registry = new ComponentRegistry([Badge::class, Button::class, Alert::class]);

        $latte = new Engine();
        $latte->setLoader(new StringLoader(['t' => $template]));
        $latte->addExtension(new RivetExtension($registry, new Renderer($registry)));

        return $latte->renderToString('t', $parameters);
    }
}
