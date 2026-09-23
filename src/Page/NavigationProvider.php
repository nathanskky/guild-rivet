<?php

declare(strict_types=1);

namespace Guild\Rivet\Page;

/**
 * Supplies the header navigation each time rvt_page renders.
 *
 * PageDefaults is configured once, but what a navigation bar should show can
 * depend on who is looking — an administration menu only its administrators
 * see, for instance. A provider passed to the Renderer is asked for the items
 * at render time, and only when a whole page is rendered.
 *
 * Rivet knows nothing about why the items differ; it renders what it is given,
 * exactly as it would PageDefaults::$navItems.
 *
 * @phpstan-import-type NavItem from PageDefaults
 */
interface NavigationProvider
{
    /**
     * The header navigation tree for this page, in the shape Header accepts.
     *
     * @return list<NavItem>
     */
    public function navItems(PageDefaults $defaults): array;
}
