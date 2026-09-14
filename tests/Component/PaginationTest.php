<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Pagination;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pagination::class)]
final class PaginationTest extends TestCase
{
    public function testEveryPageLinkIsIndividuallyLabelled(): void
    {
        $html = $this->render();

        self::assertStringContainsString('<a href="/p/1" aria-label="Page 1">1</a>', $html);
        self::assertStringContainsString(
            '<a href="/p/2" aria-label="Page 2" aria-current="page">2</a>',
            $html,
            'A bare number is not a useful link name, and the current page must say so.',
        );
    }

    public function testItIsALabelledNavigationLandmark(): void
    {
        self::assertStringStartsWith(
            '<nav role="navigation" aria-label="More pages of items"><ul class="rvt-pagination">',
            $this->render(),
        );
    }

    public function testADisabledArrowDropsTheLinkAndMovesItsLabelToTheItem(): void
    {
        $html = $this->render(previousHref: null);

        self::assertStringContainsString(
            '<li class="rvt-pagination__item" aria-label="No previous page">',
            $html,
            'Rivet marks an unavailable arrow by removing the anchor entirely rather than disabling it.',
        );
        self::assertStringNotContainsString('aria-label="Go to previous page"', $html);
    }

    public function testFirstAndLastArrowsAreOptional(): void
    {
        self::assertStringNotContainsString('Go to first page', $this->render());

        self::assertStringContainsString(
            'aria-label="Go to first page"',
            $this->render(showFirstLast: true),
            'Jump-to-end arrows suit long result sets but clutter short ones.',
        );
    }

    private function render(
        ?string $previousHref = '/p/1',
        bool $showFirstLast = false,
    ): string {
        return new Pagination(
            items: [
                ['label' => '1', 'href' => '/p/1'],
                ['label' => '2', 'href' => '/p/2', 'current' => true],
                ['label' => '3', 'href' => '/p/3'],
            ],
            previousHref: $previousHref,
            nextHref: '/p/3',
            firstHref: '/p/1',
            lastHref: '/p/9',
            showFirstLast: $showFirstLast,
        )->render(new RenderContext());
    }
}
