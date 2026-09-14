<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

/**
 * Scripts for one page, emitted just before the closing body tag, after Rivet's own.
 */
final class PageScripts extends PageSlot
{
    public static function name(): string
    {
        return 'rvt_page_scripts';
    }

    protected function give(Page $page, string $content): void
    {
        $page->addScripts($content);
    }
}
