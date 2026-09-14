<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Header;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Header::class)]
final class HeaderTest extends TestCase
{
    public function testTheSkipLinkIsTheVeryFirstThingInTheHeader(): void
    {
        self::assertStringStartsWith(
            '<header class="rvt-header-wrapper">'
            . '<a class="rvt-header-wrapper__skip-link" href="#main-content">Skip to main content</a>',
            $this->header(),
            'A keyboard user must reach the skip link before anything else, so Rivet fixes its position.',
        );
    }

    public function testTheLockupNamesTheApplicationAndLinksHome(): void
    {
        $html = $this->header();

        self::assertStringContainsString('<a class="rvt-lockup" href="/" aria-label="Course Catalog home">', $html);
        self::assertStringContainsString('<span class="rvt-lockup__title">Course Catalog</span>', $html);
        self::assertStringContainsString('<span class="rvt-lockup__subtitle">Indiana University</span>', $html);
        self::assertStringContainsString('rvt-lockup__trident', $html);
    }

    public function testThePrimaryNavIsLabelledMain(): void
    {
        self::assertStringContainsString(
            '<nav class="rvt-header-menu" aria-label="Main"',
            $this->header(),
            'A page can carry a main and a secondary nav, which must be told apart.',
        );
    }

    public function testTheCurrentPageIsMarkedOnBothTheItemAndTheLink(): void
    {
        $html = $this->header();

        self::assertStringContainsString('rvt-header-menu__item rvt-header-menu__item--current', $html);
        self::assertStringContainsString('aria-current="page"', $html);
    }

    public function testAnItemWithChildrenBecomesADropdownWithItsOwnIdentifier(): void
    {
        $html = $this->header();

        self::assertStringContainsString('data-rvt-dropdown="rvt-header-1-nav-3"', $html);
        self::assertStringContainsString('data-rvt-dropdown-toggle="rvt-header-1-nav-3"', $html);
        self::assertStringContainsString(
            'data-rvt-dropdown-menu="rvt-header-1-nav-3"',
            $html,
            'Every dropdown in the nav needs a distinct identifier, derived from the header and the position.',
        );
        self::assertStringContainsString('<a class="rvt-header-menu__submenu-link" href="/programs/chem">Chemistry</a>', $html);
    }

    public function testTheMenuToggleIsHiddenOnWideScreensAndNamed(): void
    {
        $html = $this->header();

        self::assertStringContainsString('class="rvt-global-toggle rvt-global-toggle--menu rvt-hide-lg-up"', $html);
        self::assertStringContainsString('<span class="rvt-sr-only">Menu</span>', $html);
        self::assertStringContainsString('data-rvt-disclosure-toggle="rvt-header-1-menu"', $html);
    }

    public function testSearchIsOmittedUnlessAskedFor(): void
    {
        self::assertStringNotContainsString('role="search"', $this->header());
    }

    public function testSearchRendersAsALabelledFormInsideItsOwnDisclosure(): void
    {
        $html = new Header(title: 'C', searchAction: '/search')->render(new RenderContext());

        self::assertStringContainsString('role="search"', $html);
        self::assertStringContainsString('action="/search"', $html);
        self::assertStringContainsString(
            '<label class="rvt-sr-only" for="rvt-header-1-search">Search</label>',
            $html,
            'The search field needs a label even though the design shows none.',
        );
        self::assertStringContainsString('id="rvt-header-1-search"', $html);
    }

    public function testTwoHeadersWouldNotShareIdentifiers(): void
    {
        $context = new RenderContext();
        new Header(title: 'A', searchAction: '/s')->render($context);

        self::assertStringContainsString(
            'id="rvt-header-2-search"',
            new Header(title: 'B', searchAction: '/s')->render($context),
            'Rivet documentation hard-codes id="search", which collides with anything else on the page.',
        );
    }

    private function header(): string
    {
        return new Header(
            title: 'Course Catalog',
            subtitle: 'Indiana University',
            items: [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Courses', 'href' => '/courses', 'current' => true],
                ['label' => 'Programs', 'href' => '/programs', 'children' => [
                    ['label' => 'Chemistry', 'href' => '/programs/chem'],
                ]],
            ],
        )->render(new RenderContext());
    }
}
