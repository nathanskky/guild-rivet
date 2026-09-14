<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\InvalidArgumentException;

/**
 * Maps a component's template-facing name onto its PHP class.
 *
 * Both engine integrations read from the same registry, so a component registered once
 * is available identically in Twig and Latte and the two surfaces cannot drift apart.
 */
final class ComponentRegistry
{
    /** @var array<string, class-string<Component>> */
    private array $components = [];

    /**
     * @param  list<class-string<Component>>  $components
     */
    public function __construct(array $components = [])
    {
        foreach ($components as $component) {
            $this->components[$component::name()] = $component;
        }
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->components);
    }

    /**
     * @return class-string<Component>
     *
     * @throws InvalidArgumentException
     */
    public function classFor(string $name): string
    {
        return $this->components[$name]
            ?? throw new InvalidArgumentException(sprintf('There is no Rivet component named "%s".', $name));
    }

    /**
     * Every registered name, for the engine integrations to declare their tags from.
     *
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->components);
    }
}
