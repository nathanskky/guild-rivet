<?php

declare(strict_types=1);

namespace Guild\Rivet\Latte;

use Generator;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * Compiled form of a component that takes no body, such as `{rvtBadge}`.
 */
final class RivetLeafNode extends StatementNode
{
    public string $component;

    public ArrayNode $arguments;

    public static function create(Tag $tag, string $component): static
    {
        $node = $tag->node = new static();
        $node->component = $component;
        $node->arguments = $tag->parser->parseArguments();

        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            "echo \$this->global->rivet->leaf(%dump, %node) %line;\n",
            $this->component,
            $this->arguments,
            $this->position,
        );
    }

    public function &getIterator(): Generator
    {
        // Yielding the arguments is what makes this a generator; a node with nothing to
        // yield would need Latte's `false && yield;` idiom, since `yield from []` is a
        // fatal error in a by-reference generator.
        yield $this->arguments;
    }
}
