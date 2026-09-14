<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

/**
 * The sidebar region. Its content is whatever the page needs there, most often an
 * rvt_sidenav.
 */
final class PageSidebar extends PageSlot
{
    public static function name(): string
    {
        return 'rvt_page_sidebar';
    }

    protected function give(Page $page, string $content): void
    {
        $page->setSidebar($content);
    }
}
