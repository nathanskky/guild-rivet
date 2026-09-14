<?php

declare(strict_types=1);

/**
 * Runnable demo: every highlight component, rendered through both engines, inside a full
 * Rivet page.
 *
 *     php -S localhost:8080 -t examples/public
 *
 * The page renders once with Twig and once with Latte and compares the two, which makes
 * the parity guarantee something you can see rather than only assert in a test. The
 * comparison ignores whitespace between elements: the engines treat the newline after a
 * tag differently, so the two documents differ in blank space while every component's
 * markup is identical.
 */

use Guild\Rivet\Latte\RivetExtension as LatteExtension;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\Renderer;
use Guild\Rivet\Rivet;
use Guild\Rivet\Twig\RivetExtension as TwigExtension;
use Guild\Rivet\Twig\RivetRuntime;
use Latte\Engine as LatteEngine;
use Latte\Loaders\FileLoader;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$templates = dirname(__DIR__) . '/templates';

$defaults = new PageDefaults(
    appTitle: 'Rivet for PHP',
    appSubtitle: 'Indiana University',
    navItems: [
        ['label' => 'Components', 'href' => '#', 'current' => true],
    ],
);

$renderWithTwig = static function () use ($templates, $defaults): string {
    $registry = Rivet::registry();
    $renderer = new Renderer($registry, pageDefaults: $defaults);

    $twig = new Environment(new FilesystemLoader($templates));
    $twig->addExtension(new TwigExtension($registry));
    $twig->addRuntimeLoader(new FactoryRuntimeLoader([
        RivetRuntime::class => static fn (): RivetRuntime => new RivetRuntime($renderer),
    ]));

    return $twig->render('page.html.twig');
};

$renderWithLatte = static function () use ($templates, $defaults): string {
    $registry = Rivet::registry();
    $renderer = new Renderer($registry, pageDefaults: $defaults);

    $latte = new LatteEngine();
    $latte->setLoader(new FileLoader($templates));
    $latte->setTempDirectory(sys_get_temp_dir() . '/guild-rivet-demo');
    $latte->addExtension(new LatteExtension($registry, $renderer));

    return $latte->renderToString('page.latte');
};

$engine = $_GET['engine'] ?? 'twig';
$page = $engine === 'latte' ? $renderWithLatte() : $renderWithTwig();

/**
 * Compare the two engines with the whitespace *between* elements collapsed.
 *
 * Component markup is byte-identical and the parity test asserts exactly that. Whole-page
 * output is not, and should not be expected to be: Twig swallows the newline after `%}`
 * while Latte keeps it, so the two documents differ in the blank space between sibling
 * components. That is template formatting rather than anything a component produced.
 */
$collapse = static fn (string $html): string => trim((string) preg_replace('/>\s+</', '><', $html));

$identical = $collapse($renderWithTwig()) === $collapse($renderWithLatte());

$banner = '<div class="rvt-container rvt-p-tb-xs ' . ($identical ? 'rvt-bg-green-000' : 'rvt-bg-orange-000') . '">'
    . '<p class="rvt-ts-14 rvt-m-all-remove">'
    . 'Rendered with <strong>' . htmlspecialchars($engine, ENT_QUOTES) . '</strong> · '
    . '<a href="?engine=twig">Twig</a> · <a href="?engine=latte">Latte</a> · '
    . ($identical
        ? 'both engines produced identical markup, ignoring whitespace between elements'
        : '<strong>the two engines produced different markup — this is a bug</strong>')
    . '</p></div>';

$page = str_replace('<body class="rvt-layout">', '<body class="rvt-layout">' . $banner, $page, $count);

if ($count !== 1) {
    throw new RuntimeException('Expected exactly one <body class="rvt-layout"> to inject the banner after.');
}

echo $page;
