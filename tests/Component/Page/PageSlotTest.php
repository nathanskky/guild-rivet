<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Component\Page\PageBreadcrumbs;
use Guild\Rivet\Component\Page\PageScripts;
use Guild\Rivet\Component\Page\PageSidebar;
use Guild\Rivet\Component\Page\PageStyles;
use Guild\Rivet\Enum\PageLayout;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageStyles::class)]
#[CoversClass(PageScripts::class)]
#[CoversClass(PageBreadcrumbs::class)]
#[CoversClass(PageSidebar::class)]
final class PageSlotTest extends TestCase
{
    public function testASlotRendersNothingWhereItIsWritten(): void
    {
        $context = $this->context();
        $context->open(new Page());

        self::assertSame(
            '',
            new PageStyles()->render($context, '<link rel="stylesheet" href="/a.css">'),
            'A slot registers its content with the page rather than emitting it in place.',
        );
    }

    public function testStylesLandInTheHeadAndScriptsAtTheEnd(): void
    {
        $context = $this->context();
        $page = new Page();
        $context->open($page);

        new PageStyles()->render($context, '<link rel="stylesheet" href="/a.css">');
        new PageScripts()->render($context, '<script src="/a.js"></script>');

        $html = $page->render($context, '<p>Body</p>');

        self::assertStringContainsString(
            '<link rel="stylesheet" href="/a.css"></head>',
            $html,
            'Styles registered through the slot are emitted at the end of the head.',
        );
        self::assertStringContainsString(
            '<script src="/a.js"></script></body>',
            $html,
            'Scripts registered through the slot are emitted just before the closing body tag.',
        );
    }

    public function testBreadcrumbsLandInTheHeadingBandAboveTheHeading(): void
    {
        $context = $this->context();
        $page = new Page(heading: 'Chemistry');
        $context->open($page);

        new PageBreadcrumbs()->render($context, '<nav>crumbs</nav>');

        self::assertStringContainsString(
            '<nav>crumbs</nav><h1 class="rvt-m-top-xs">Chemistry</h1>',
            $page->render($context, 'x'),
            'Breadcrumbs registered through the slot are emitted in the heading band, before the heading.',
        );
    }

    public function testBreadcrumbsAloneStillProduceTheBand(): void
    {
        $context = $this->context();
        $page = new Page();
        $context->open($page);

        new PageBreadcrumbs()->render($context, '<nav>crumbs</nav>');

        self::assertStringContainsString(
            'rvt-border-bottom',
            $page->render($context, 'x'),
            'Breadcrumbs alone are enough to produce the heading band, even with no heading text.',
        );
    }

    public function testTwoSidebarSlotsInTheSamePageAreRejectedRatherThanSilentlyDiscardingTheFirst(): void
    {
        $context = $this->context();
        $page = new Page(layout: PageLayout::Sidebar);
        $context->open($page);

        new PageSidebar()->render($context, '<nav>first</nav>');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_page_sidebar can only be used once. A page has one sidebar.');

        new PageSidebar()->render($context, '<nav>second</nav>');
    }

    public function testTwoBreadcrumbsSlotsInTheSamePageAreRejectedRatherThanSilentlyDiscardingTheFirst(): void
    {
        $context = $this->context();
        $page = new Page();
        $context->open($page);

        new PageBreadcrumbs()->render($context, '<nav>first</nav>');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_page_breadcrumbs can only be used once. A page has one set of breadcrumbs.');

        new PageBreadcrumbs()->render($context, '<nav>second</nav>');
    }

    public function testASlotOutsideAPageIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_page_styles must be used inside rvt_page.');

        new PageStyles()->render($this->context(), 'x');
    }

    private function context(): RenderContext
    {
        return new RenderContext(pageDefaults: new PageDefaults(appTitle: 'Course Catalog'));
    }
}
