<?php

declare(strict_types=1);

namespace Guild\Rivet\Test;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Component\Button;
use Guild\Rivet\Latte\RivetExtension as LatteExtension;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Twig\RivetExtension as TwigExtension;
use Guild\Rivet\Twig\RivetRuntime;
use Latte\Engine as LatteEngine;
use Latte\Loaders\StringLoader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * The guard that keeps the two engine surfaces honest.
 *
 * Both compile down to the same PHP renderer, so identical inputs must produce identical
 * bytes. Anything that drifts — a tag parsed differently, whitespace handled differently —
 * shows up here rather than in someone's page.
 *
 * Deliberately scoped to markup the components generate. Template interpolation is NOT
 * compared: Twig escapes with ENT_QUOTES while Latte uses ENT_NOQUOTES and rewrites `{`
 * to `&#123;`, which is engine-level behaviour neither should override. Data belongs in
 * component arguments, where this library does the escaping.
 */
final class CrossEngineParityTest extends TestCase
{
    #[DataProvider('equivalentTemplates')]
    public function testTwigAndLatteEmitIdenticalMarkup(string $twig, string $latte): void
    {
        $fromTwig = $this->renderTwig($twig);

        self::assertNotSame('', $fromTwig, 'A case that renders nothing would pass vacuously.');
        self::assertSame(
            $fromTwig,
            $this->renderLatte($latte),
            'Both engines call the same renderer, so their output must match byte for byte.',
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function equivalentTemplates(): iterable
    {
        yield 'leaf component' => [
            "{{ rvt_badge('New', style: 'info') }}",
            "{rvtBadge 'New', style: 'info'}",
        ];

        yield 'leaf with extra attributes' => [
            "{{ rvt_badge('New', data_testid: 'b', class: 'rvt-m-top-md') }}",
            "{rvtBadge 'New', data_testid: 'b', class: 'rvt-m-top-md'}",
        ];

        yield 'block component in its short form' => [
            "{{ rvt_button('Delete', purpose: 'danger') }}",
            "{rvtButton 'Delete', purpose: 'danger' /}",
        ];

        yield 'block component with an icon body' => [
            '{% rvt_button purpose="success" %}<svg></svg><span>Add</span>{% endrvt_button %}',
            "{rvtButton purpose: 'success'}<svg></svg><span>Add</span>{/rvtButton}",
        ];

        yield 'block component' => [
            "{% rvt_alert title='Notice' dismissible=false %}<p>Body</p>{% endrvt_alert %}",
            "{rvtAlert title: 'Notice', dismissible: false}<p>Body</p>{/rvtAlert}",
        ];

        yield 'block component indented across lines' => [
            "{% rvt_alert title='Notice' dismissible=false %}\n    <p>Body</p>\n{% endrvt_alert %}",
            "{rvtAlert title: 'Notice', dismissible: false}\n    <p>Body</p>\n{/rvtAlert}",
        ];

        yield 'dismissible block with embedded svg' => [
            "{% rvt_alert title='Heads up' %}<p>Body</p>{% endrvt_alert %}",
            "{rvtAlert title: 'Heads up'}<p>Body</p>{/rvtAlert}",
        ];

        yield 'nested blocks each taking an id' => [
            "{% rvt_alert title='Outer' dismissible=false %}{% rvt_alert title='Inner' dismissible=false %}x{% endrvt_alert %}{% endrvt_alert %}",
            "{rvtAlert title: 'Outer', dismissible: false}{rvtAlert title: 'Inner', dismissible: false}x{/rvtAlert}{/rvtAlert}",
        ];

        yield 'sibling blocks taking successive ids' => [
            "{% rvt_alert title='A' dismissible=false %}a{% endrvt_alert %}{% rvt_alert title='B' dismissible=false %}b{% endrvt_alert %}",
            "{rvtAlert title: 'A', dismissible: false}a{/rvtAlert}{rvtAlert title: 'B', dismissible: false}b{/rvtAlert}",
        ];
    }

    private static function registry(): ComponentRegistry
    {
        return new ComponentRegistry([Badge::class, Button::class, Alert::class]);
    }

    private function renderTwig(string $template): string
    {
        $registry = self::registry();
        $renderer = new Renderer($registry);

        $twig = new Environment(new ArrayLoader(['t' => $template]));
        $twig->addExtension(new TwigExtension($registry));
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
        ]));

        return $twig->render('t');
    }

    private function renderLatte(string $template): string
    {
        $registry = self::registry();

        $latte = new LatteEngine();
        $latte->setLoader(new StringLoader(['t' => $template]));
        $latte->addExtension(new LatteExtension($registry, new Renderer($registry)));

        return $latte->renderToString('t');
    }
}
