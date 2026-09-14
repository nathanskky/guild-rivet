<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

/**
 * Breadcrumbs for one page, emitted in the heading band above the page heading — or
 * inside the content region under the anchored sidebar layout, which has no band.
 */
final class PageBreadcrumbs extends PageSlot
{
    public static function name(): string
    {
        return 'rvt_page_breadcrumbs';
    }

    protected function give(Page $page, string $content): void
    {
        $page->setBreadcrumbs($content);
    }
}
