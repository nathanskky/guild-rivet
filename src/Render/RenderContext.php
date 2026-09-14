<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Page\PageDefaults;

/**
 * Tracks which components are currently open, and hands out element ids.
 *
 * This is what lets a child component read something from its parent — a dialog's close
 * button deriving the id it must reference, a card body confirming it really is inside a
 * card. Rivet requires the same identifier in up to five places on a single component,
 * and its own documentation ships literal ids such as `dialog-title`, so two examples
 * copied onto one page are already broken. Deriving those ids from one generated value
 * is the whole point.
 *
 * Instantiate one per render, never once globally. Within a single render a component
 * that throws mid-body would otherwise leave a stale frame behind and corrupt every
 * later render on the page; the engine integrations pop in a `finally` for that reason.
 */
final class RenderContext
{
    /** @var list<Component> Innermost component last. */
    private array $stack = [];

    private readonly IdGenerator $ids;

    public function __construct(
        ?IdGenerator $ids = null,
        private readonly ?PageDefaults $pageDefaults = null,
    ) {
        $this->ids = $ids ?? new SequentialIdGenerator();
    }

    public function ids(): IdGenerator
    {
        return $this->ids;
    }

    /**
     * Application-wide page configuration, or null when none was supplied.
     */
    public function pageDefaults(): ?PageDefaults
    {
        return $this->pageDefaults;
    }

    public function open(Component $component): void
    {
        $this->stack[] = $component;
    }

    public function close(): void
    {
        array_pop($this->stack);
    }

    /**
     * Whether nothing is currently open.
     */
    public function isEmpty(): bool
    {
        return $this->stack === [];
    }

    /**
     * The innermost open component of the given type, or null if there is none.
     *
     * @template TComponent of Component
     *
     * @param  class-string<TComponent>  $componentClass
     * @return TComponent|null
     */
    public function closest(string $componentClass): ?Component
    {
        foreach (array_reverse($this->stack) as $open) {
            if ($open instanceof $componentClass) {
                return $open;
            }
        }

        return null;
    }

    /**
     * The innermost open component of the given type, or a failure naming both parties.
     *
     * @template TComponent of Component
     *
     * @param  class-string<TComponent>  $componentClass  the required parent
     * @param  class-string<Component>  $usedBy  the child that requires it, named in the error
     * @return TComponent
     *
     * @throws ComponentContextException
     */
    public function requireAncestor(string $componentClass, string $usedBy): Component
    {
        $found = $this->closest($componentClass);

        if ($found === null) {
            throw new ComponentContextException(
                sprintf('%s must be used inside %s.', $usedBy::name(), $componentClass::name()),
            );
        }

        return $found;
    }
}
