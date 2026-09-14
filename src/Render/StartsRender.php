<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

/**
 * Marks a component that begins a render — in practice, a whole document.
 *
 * Opening one resets the render context, which is the only point early enough to matter:
 * a component's children render before it does, so a reset performed during its own
 * render would come too late for anything nested inside it.
 *
 * Neither Twig nor Latte offers a reliable "top-level render started" hook, so the
 * outermost component is the signal.
 */
interface StartsRender
{
}
