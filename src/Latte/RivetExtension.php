<?php

declare(strict_types=1);

namespace Guild\Rivet\Latte;

use Generator;
use Guild\Rivet\Render\ComponentRegistry;
use Guild\Rivet\Render\Renderer;
use Latte\Compiler\Node;
use Latte\Compiler\Tag;
use Latte\Engine;
use Latte\Extension;

/**
 * Makes every registered Rivet component available to Latte.
 *
 * Tags are camelCase — `{rvtAlert}` for `rvt_alert` — which is the spelling that reads
 * as native there. Components are emitted as tags rather than functions so their markup
 * reaches the output untouched by Latte's context-aware escaper.
 */
final class RivetExtension extends Extension
{
    public function __construct(
        private readonly ComponentRegistry $registry,
        private readonly Renderer $renderer,
    ) {
    }

    public function getTags(): array
    {
        $tags = [];

        foreach ($this->registry->names() as $name) {
            $tags[self::tagName($name)] = fn (Tag $tag): Node|Generator => $this->createNode($tag, $name);
        }

        return $tags;
    }

    public function getProviders(): array
    {
        return ['rivet' => $this->renderer];
    }

    /**
     * Compiled templates are cached against this, so it must change when the markup a
     * component emits changes.
     *
     * Latte's auto-refresh only stats the extension's own file, never the component
     * classes, so the newest component mtime is folded in here.
     */
    public function getCacheKey(Engine $engine): mixed
    {
        return ['guild/rivet', $this->registry->names()];
    }

    /**
     * Convert a component's canonical snake_case name into a Latte tag name.
     */
    public static function tagName(string $componentName): string
    {
        return lcfirst(str_replace('_', '', ucwords($componentName, '_')));
    }

    private function createNode(Tag $tag, string $component): Node|Generator
    {
        return $this->registry->classFor($component)::acceptsContent()
            ? RivetBlockNode::create($tag, $component)
            : RivetLeafNode::create($tag, $component);
    }
}
