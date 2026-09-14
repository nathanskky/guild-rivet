<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Component\Page\PageBreadcrumbs;
use Guild\Rivet\Component\Page\PageSidebar;
use Guild\Rivet\Enum\PageLayout;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Page::class)]
final class PageLayoutTest extends TestCase
{
    public function testTheSidebarLayoutPutsTheWrapperInsideMainWithAContainer(): void
    {
        $html = $this->render(PageLayout::Sidebar, '<nav>side</nav>', '<p>Body</p>');

        self::assertStringContainsString(
            '<div class="rvt-layout__wrapper rvt-layout__wrapper--details rvt-container-lg">'
            . '<div class="rvt-layout__sidebar rvt-p-top-xxl rvt-flow rvt-prose" id="section-nav"><nav>side</nav></div>'
            . '<div class="rvt-layout__content rvt-p-top-xxl"><p>Body</p></div>'
            . '</div>',
            $html,
            'The contained sidebar layout carries the wrapper, sidebar and content classes verbatim from Rivet.',
        );
        self::assertStringContainsString(
            '<main id="main-content" class="rvt-flex rvt-flex-column rvt-grow-1">',
            $html,
            'With a contained sidebar, main is still the flex column above the wrapper.',
        );
    }

    public function testTheAnchoredLayoutMakesMainTheWrapper(): void
    {
        $html = $this->render(PageLayout::AnchoredSidebar, '<nav>side</nav>', '<p>Body</p>');

        self::assertStringContainsString(
            '<main id="main-content" class="rvt-layout__wrapper rvt-layout__wrapper--details">',
            $html,
            'Anchoring the sidebar to the viewport edge means main becomes the wrapper itself.',
        );
        self::assertStringContainsString(
            '<div class="rvt-layout__sidebar rvt-p-top-xxl rvt-p-left-md rvt-bg-black-000" id="section-nav">',
            $html,
            'The anchored sidebar runs flush to the viewport edge, so it carries its own background rather than sharing the page container.',
        );
    }

    public function testTheAnchoredLayoutHasNoHeadingBandAndPutsTheHeadingInTheContent(): void
    {
        $context = $this->context();
        $page = new Page(heading: 'Chemistry', layout: PageLayout::AnchoredSidebar);
        $context->open($page);
        new PageSidebar()->render($context, '<nav>side</nav>');
        new PageBreadcrumbs()->render($context, '<nav>crumbs</nav>');

        $html = $page->render($context, '<p>Body</p>');

        self::assertStringNotContainsString(
            'rvt-border-bottom',
            $html,
            'Main is the wrapper here, so there is nowhere above it for a full-bleed band.',
        );
        self::assertStringContainsString(
            '<div class="rvt-prose"><nav>crumbs</nav><h1 class="rvt-m-top-xs">Chemistry</h1></div>',
            $html,
            'Breadcrumbs and the heading move into the content region instead of the omitted band.',
        );
    }

    public function testASidebarUnderTheSingleColumnLayoutIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'rvt_page_sidebar has nowhere to go in the single_column layout. Use the sidebar or anchored_sidebar layout.'
        );

        $this->render(PageLayout::SingleColumn, '<nav>side</nav>', 'x');
    }

    private function render(PageLayout $layout, string $sidebar, string $content): string
    {
        $context = $this->context();
        $page = new Page(layout: $layout);
        $context->open($page);
        new PageSidebar()->render($context, $sidebar);

        return $page->render($context, $content);
    }

    private function context(): RenderContext
    {
        return new RenderContext(pageDefaults: new PageDefaults(appTitle: 'Course Catalog'));
    }
}
