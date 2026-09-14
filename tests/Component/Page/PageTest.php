<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component\Page;

use Guild\Rivet\Component\Page\Page;
use Guild\Rivet\Enum\PageLayout;
use Guild\Rivet\Exception\ConfigurationException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Page\RivetAssets;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Page::class)]
final class PageTest extends TestCase
{
    public function testTheDocumentOpensCorrectly(): void
    {
        self::assertStringStartsWith(
            '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Chemistry · Course Catalog</title>',
            $this->render(new Page(title: 'Chemistry')),
            'The page owns the whole document, so nothing above body is left to the application.',
        );
    }

    public function testThePageTitleIsOptional(): void
    {
        self::assertStringContainsString(
            '<title>Course Catalog</title>',
            $this->render(new Page()),
            'A page with no title of its own is named by the application.',
        );
    }

    public function testRivetAssetsAreEmittedByDefault(): void
    {
        $html = $this->render(new Page());

        self::assertStringContainsString(
            'href="https://unpkg.com/rivet-core@2.9.1/css/rivet.min.css"',
            $html,
            'Rivet\'s stylesheet is served from the CDN unless the application opts out.',
        );
        self::assertStringContainsString(
            'src="https://unpkg.com/rivet-core@2.9.1/js/rivet.min.js"',
            $html,
            'Rivet\'s script is served from the CDN unless the application opts out.',
        );
        self::assertStringContainsString('<script>Rivet.init()</script>', $html, 'Rivet does nothing until init is called.');
        self::assertStringContainsString(
            'rivet-icons@3.0.1',
            $html,
            'The icon font is included alongside the core assets unless the application opts out.',
        );
    }

    public function testAssetsCanBeTurnedOff(): void
    {
        $html = $this->render(new Page(), new PageDefaults(
            appTitle: 'Course Catalog',
            assets: new RivetAssets(enabled: false, icons: false),
        ));

        self::assertStringNotContainsString('unpkg.com', $html, 'An application serving Rivet itself must be able to opt out.');
        self::assertStringNotContainsString(
            'Rivet.init()',
            $html,
            'There is nothing to initialize once the assets themselves are turned off.',
        );
    }

    public function testTheBodyCarriesTheLayoutClassAndTheContentIsInsideMain(): void
    {
        $html = $this->render(new Page(), content: '<p>Body</p>');

        self::assertStringContainsString(
            '<body class="rvt-layout">',
            $html,
            'The layout class on body is what Rivet\'s blank-page styles target.',
        );
        self::assertStringContainsString(
            '<main id="main-content" class="rvt-flex rvt-flex-column rvt-grow-1">',
            $html,
            'Rivet puts main outside the layout wrapper, which is what makes the header skip link land correctly.',
        );
        self::assertStringContainsString(
            '<div class="rvt-layout__wrapper rvt-p-tb-xxl"><div class="rvt-container-lg"><p>Body</p></div></div>',
            $html,
            'The page content is placed inside the container before it reaches the application.',
        );
    }

    public function testTheHeadingBandAppearsOnlyWhenThereIsAHeading(): void
    {
        self::assertStringNotContainsString(
            'rvt-border-bottom',
            $this->render(new Page()),
            'A page with neither heading nor breadcrumbs has nothing to put in the band.',
        );

        self::assertStringContainsString(
            '<div class="rvt-bg-black-000 rvt-border-bottom rvt-p-top-xl">'
            . '<div class="rvt-container-lg rvt-prose rvt-flow rvt-p-bottom-xl">'
            . '<h1 class="rvt-m-top-xs">Chemistry</h1>'
            . '</div></div>',
            $this->render(new Page(heading: 'Chemistry')),
            'A heading alone is enough to render the band.',
        );
    }

    public function testTheHeaderAndFooterComeFromTheDefaults(): void
    {
        $html = $this->render(new Page());

        self::assertStringContainsString(
            '<span class="rvt-lockup__title">Course Catalog</span>',
            $html,
            'The header is built from the application-wide defaults, not repeated per page.',
        );
        self::assertStringContainsString(
            '<span class="rvt-lockup__subtitle">Indiana University</span>',
            $html,
            'The header subtitle also comes from the application-wide defaults.',
        );
        self::assertStringContainsString(
            'rvt-footer-base',
            $html,
            'The footer is rendered from the same application-wide defaults as the header.',
        );
    }

    public function testADescriptionIsEmittedAndThePageOverridesTheApplication(): void
    {
        $html = $this->render(
            new Page(description: 'All chemistry courses'),
            new PageDefaults(
                appTitle: 'Course Catalog',
                description: 'A catalog of every course the university offers.',
            ),
        );

        self::assertStringContainsString(
            '<meta name="description" content="All chemistry courses">',
            $html,
            'A description passed to the page is emitted as the document description.',
        );
        self::assertStringNotContainsString(
            'A catalog of every course the university offers.',
            $html,
            'A page-level description takes precedence over the application-wide default.',
        );
    }

    public function testASidebarSetOnTheSingleColumnLayoutIsRejected(): void
    {
        $page = new Page();
        $page->setSidebar('<nav>side</nav>');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'rvt_page_sidebar has nowhere to go in the single_column layout. Use the sidebar or anchored_sidebar layout.'
        );

        $this->render($page);
    }

    #[DataProvider('layoutsWithASidebarRegion')]
    public function testASidebarIsNotRejectedUnderALayoutThatHasSomewhereToPutIt(PageLayout $layout): void
    {
        $page = new Page(layout: $layout);
        $page->setSidebar('<nav>side</nav>');

        self::assertStringContainsString(
            '<body class="rvt-layout">',
            $this->render($page),
            'The single-column guard must name the layout it actually rejects, so a sidebar layout is unaffected by it.',
        );
    }

    /**
     * @return iterable<string, array{PageLayout}>
     */
    public static function layoutsWithASidebarRegion(): iterable
    {
        yield 'sidebar' => [PageLayout::Sidebar];
        yield 'anchored sidebar' => [PageLayout::AnchoredSidebar];
    }

    public function testAPageWithoutConfiguredDefaultsSaysHowToFixIt(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(
            'rvt_page needs PageDefaults. Pass one to the Renderer constructor, or to addRivet() in a Guild application.'
        );

        new Page()->render(new RenderContext());
    }

    private function render(Page $page, ?PageDefaults $defaults = null, string $content = ''): string
    {
        return $page->render(
            new RenderContext(pageDefaults: $defaults ?? new PageDefaults(
                appTitle: 'Course Catalog',
                appSubtitle: 'Indiana University',
            )),
            $content,
        );
    }
}
