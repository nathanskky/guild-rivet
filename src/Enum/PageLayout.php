<?php

declare(strict_types=1);

namespace Guild\Rivet\Enum;

/**
 * Which blank-page layout a page uses.
 *
 * These are structurally different documents rather than one structure with modifiers.
 * Under AnchoredSidebar `<main>` becomes the layout wrapper itself, which leaves nowhere
 * above it for the full-bleed heading band, so the heading moves inside the content.
 *
 * @see https://rivet.iu.edu/layouts/blank-page/
 */
enum PageLayout: string
{
    case SingleColumn = 'single_column';
    case Sidebar = 'sidebar';
    case AnchoredSidebar = 'anchored_sidebar';
}
