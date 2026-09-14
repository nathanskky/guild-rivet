<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Breadcrumbs;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Breadcrumbs::class)]
final class BreadcrumbsTest extends TestCase
{
    public function testTheFinalCrumbIsTheCurrentPageAndIsNotALink(): void
    {
        $html = new Breadcrumbs([
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Programs', 'href' => '/programs'],
            ['label' => 'Chemistry'],
        ])->render(new RenderContext());

        self::assertStringContainsString('<li><a href="/programs">Programs</a></li>', $html);
        self::assertStringContainsString(
            '<li aria-current="page">Chemistry</li>',
            $html,
            'The page you are on is not a link to itself, and carries aria-current.',
        );
    }

    public function testTheTrailIsALabelledNavigationLandmark(): void
    {
        self::assertStringStartsWith(
            '<nav role="navigation" aria-label="Breadcrumbs"><ol class="rvt-breadcrumbs">',
            new Breadcrumbs([['label' => 'Home']])->render(new RenderContext()),
            'Several navigation landmarks on a page need distinguishing labels.',
        );
    }

    public function testAHomeCrumbCanRenderAsAnIconWithScreenReaderText(): void
    {
        $html = new Breadcrumbs([
            ['label' => 'Home', 'href' => '/', 'icon' => true],
            ['label' => 'Here'],
        ])->render(new RenderContext());

        self::assertStringContainsString('<span class="rvt-sr-only">Home</span>', $html, 'An icon-only link still needs a name.');
        self::assertStringContainsString('<svg ', $html);
    }

    public function testAnEmptyTrailIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_breadcrumbs needs at least one item.');

        new Breadcrumbs([])->render(new RenderContext());
    }

    public function testACrumbWithoutALabelIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Breadcrumb 2 needs a "label".');

        new Breadcrumbs([['label' => 'Home'], ['href' => '/x']])->render(new RenderContext());
    }
}
