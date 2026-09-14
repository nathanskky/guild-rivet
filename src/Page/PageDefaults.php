<?php

declare(strict_types=1);

namespace Guild\Rivet\Page;

use Guild\Rivet\Enum\ContainerSize;

/**
 * Everything a page needs that is identical on every page of an application.
 *
 * Configured once and reached through RenderContext, so a page template carries only
 * what is specific to that page. Only the fields applications actually vary appear here:
 * Footer already defaults IU's required accessibility, privacy and copyright links, so
 * those are not repeated.
 *
 * @phpstan-type NavItem array{label?: string, href?: string, current?: bool, children?: array<int, mixed>}
 */
final readonly class PageDefaults
{
    /**
     * @param  list<NavItem>  $navItems  header navigation tree, as Header accepts
     * @param  list<array{label?: string, href?: string}>  $footerLinks  extra footer links
     */
    public function __construct(
        public string $appTitle,
        public ?string $appSubtitle = null,
        public string $homeHref = '/',
        public array $navItems = [],
        public ?string $searchAction = null,
        public array $footerLinks = [],
        public bool $footerLight = false,
        public ContainerSize $containerSize = ContainerSize::Large,
        public string $lang = 'en',
        public string $titleSeparator = ' · ',
        public ?string $description = null,
        public RivetAssets $assets = new RivetAssets(),
    ) {
    }

    /**
     * The document title: the page's own, then the application's.
     */
    public function documentTitle(?string $pageTitle): string
    {
        if ($pageTitle === null || $pageTitle === '') {
            return $this->appTitle;
        }

        return $pageTitle . $this->titleSeparator . $this->appTitle;
    }
}
