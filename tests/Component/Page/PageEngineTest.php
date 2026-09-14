<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Latte\RivetExtension as LatteExtension;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Rivet;
use Guild\Rivet\Twig\RivetExtension as TwigExtension;
use Guild\Rivet\Twig\RivetRuntime;
use Latte\Engine as LatteEngine;
use Latte\Loaders\StringLoader;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

final class PageEngineTest extends TestCase
{
    private const string TWIG = <<<'TWIG'
        {% rvt_page title="Chemistry" heading="Chemistry" layout="sidebar" %}
        {% rvt_page_sidebar %}<nav>side</nav>{% endrvt_page_sidebar %}
        {% rvt_page_styles %}<link rel="stylesheet" href="/a.css">{% endrvt_page_styles %}
        <p>Body</p>
        {% endrvt_page %}
        TWIG;

    private const string LATTE = <<<'LATTE'
        {rvtPage title: 'Chemistry', heading: 'Chemistry', layout: 'sidebar'}
        {rvtPageSidebar}<nav>side</nav>{/rvtPageSidebar}
        {rvtPageStyles}<link rel="stylesheet" href="/a.css">{/rvtPageStyles}
        <p>Body</p>
        {/rvtPage}
        LATTE;

    public function testAPageRendersThroughTwig(): void
    {
        $html = $this->twig(self::TWIG);

        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('<link rel="stylesheet" href="/a.css"></head>', $html, 'A slot reaches the head.');
        self::assertStringContainsString('<nav>side</nav>', $html);
        self::assertStringContainsString('<p>Body</p>', $html);
    }

    public function testAPageRendersThroughLatte(): void
    {
        self::assertStringStartsWith('<!doctype html>', $this->latte(self::LATTE));
    }

    public function testBothEnginesAssembleTheSameDocument(): void
    {
        self::assertSame(
            $this->twig(self::TWIG),
            $this->latte(self::LATTE),
            'Both compile to the same renderer, so the assembled document must match byte for byte.',
        );
    }

    private static function defaults(): PageDefaults
    {
        return new PageDefaults(appTitle: 'Course Catalog', appSubtitle: 'Indiana University');
    }

    private function twig(string $template): string
    {
        $registry = Rivet::registry();
        $renderer = new Renderer($registry, pageDefaults: self::defaults());

        $twig = new Environment(new ArrayLoader(['t' => $template]));
        $twig->addExtension(new TwigExtension($registry));
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
        ]));

        return $twig->render('t');
    }

    private function latte(string $template): string
    {
        $registry = Rivet::registry();

        $latte = new LatteEngine();
        $latte->setLoader(new StringLoader(['t' => $template]));
        $latte->addExtension(new LatteExtension($registry, new Renderer($registry, pageDefaults: self::defaults())));

        return $latte->renderToString('t');
    }
}
