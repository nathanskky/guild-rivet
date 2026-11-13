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
     * @param  string  $appTitle  The app's name, shown in the header and after each page's `<title>`.
     * @param  string|null  $appSubtitle  A line under the app's name in the header.
     * @param  string  $homeHref  Where the header's app name links.
     * @param  list<NavItem>  $navItems  Header navigation tree, as Header accepts. A NavigationProvider
     *     passed to the Renderer can change it per page.
     * @param  string|null  $searchAction  The URL the header's search form submits to; null for no search form.
     * @param  list<array{label?: string, href?: string}>  $footerLinks  Links added to the footer. IU's
     *     required links are always present.
     * @param  bool  $footerLight  Whether the footer uses Rivet's light style.
     * @param  ContainerSize  $containerSize  Width of the page's content container.
     * @param  string  $lang  The document's `lang` attribute.
     * @param  string  $titleSeparator  Placed between the page's title and the app's in `<title>`.
     * @param  string|null  $description  The default meta description.
     * @param  RivetAssets  $assets  Where the page loads Rivet's CSS, JavaScript and icons from.
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
