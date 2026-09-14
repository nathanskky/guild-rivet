<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Footer;
use Guild\Rivet\Component\StepIndicator;
use Guild\Rivet\Component\Subnav;
use Guild\Rivet\Enum\StepStatus;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Subnav::class)]
#[CoversClass(StepIndicator::class)]
#[CoversClass(Footer::class)]
final class NavigationTest extends TestCase
{
    public function testASubnavIsADistinctlyLabelledLandmark(): void
    {
        self::assertSame(
            '<nav class="rvt-subnav" aria-label="Section navigation">'
            . '<ul class="rvt-subnav__list">'
            . '<li class="rvt-subnav__item"><a href="#overview">Overview</a></li>'
            . '<li class="rvt-subnav__item"><a href="#usage" aria-current="page">Usage</a></li>'
            . '</ul></nav>',
            new Subnav(items: [
                ['label' => 'Overview', 'href' => '#overview'],
                ['label' => 'Usage', 'href' => '#usage', 'current' => true],
            ])->render(new RenderContext()),
            'A page can hold several navigation landmarks, so each needs its own name.',
        );
    }

    public function testStepsAreNumberedAndTheCurrentOneIsMarked(): void
    {
        $html = new StepIndicator(steps: [
            ['label' => 'Create username', 'href' => '/1'],
            ['label' => 'Personal information', 'href' => '/2', 'current' => true],
        ])->render(new RenderContext());

        self::assertStringContainsString(
            '<span class="rvt-steps__indicator"><span class="rvt-sr-only">Step</span> 1</span>',
            $html,
            'Rivet names the sequence once, on the first indicator, so it is not repeated at every number.',
        );
        self::assertStringContainsString('<span class="rvt-steps__indicator">2</span>', $html);
        self::assertStringContainsString('aria-current="step"', $html);
    }

    public function testAStepCanCarryAStatus(): void
    {
        self::assertStringContainsString(
            'class="rvt-steps__indicator rvt-steps__indicator--success"',
            new StepIndicator(steps: [['label' => 'Done', 'href' => '/1', 'status' => StepStatus::Success]])
                ->render(new RenderContext()),
        );
    }

    public function testStepsCanBeStackedVertically(): void
    {
        self::assertStringStartsWith(
            '<ol class="rvt-steps rvt-steps--vertical">',
            new StepIndicator(steps: [['label' => 'One', 'href' => '/1']], vertical: true)
                ->render(new RenderContext()),
        );
    }

    public function testTheFooterCarriesTheLinksIuRequires(): void
    {
        $html = new Footer(year: 2026)->render(new RenderContext());

        self::assertStringContainsString('>Accessibility</a>', $html);
        self::assertStringContainsString('>Privacy Notice</a>', $html);
        self::assertStringContainsString(
            '© 2026 The Trustees of',
            $html,
            'IU requires accessibility and privacy links and a current copyright line in every footer.',
        );
        self::assertStringContainsString('<polygon', $html, 'The trident must appear unmodified.');
    }

    public function testTheFooterHasALightVariant(): void
    {
        self::assertStringStartsWith(
            '<footer class="rvt-footer-base rvt-footer-base--light">',
            new Footer(light: true, year: 2026)->render(new RenderContext()),
        );
    }

    public function testExtraFooterLinksAppearBeforeTheCopyright(): void
    {
        $html = new Footer(links: [['label' => 'Contact', 'href' => '/contact']], year: 2026)
            ->render(new RenderContext());

        self::assertLessThan(
            strpos($html, '© 2026'),
            (int) strpos($html, '>Contact</a>'),
            'The copyright line closes the list, so anything added sits above it.',
        );
    }
}
