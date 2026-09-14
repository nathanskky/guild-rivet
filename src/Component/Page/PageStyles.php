<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

/**
 * Stylesheets and inline styles for one page, emitted at the end of the head.
 */
final class PageStyles extends PageSlot
{
    public static function name(): string
    {
        return 'rvt_page_styles';
    }

    protected function give(Page $page, string $content): void
    {
        $page->addStyles($content);
    }
}
