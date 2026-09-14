<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

/**
 * Base class for every Rivet component.
 *
 * Components are plain PHP objects. They know nothing about Twig or Latte, so they are
 * equally usable from a controller, a mailer, or a test.
 */
abstract class Component
{
    /**
     * The component's canonical name in snake_case, such as `rvt_dialog_close`.
     *
     * Each engine derives its own tag spelling from this, and it is what appears in
     * error messages, so it should read the way a developer writes it in a template.
     */
    abstract public static function name(): string;
}
