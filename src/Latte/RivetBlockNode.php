<?php

declare(strict_types=1);

namespace Guild\Rivet\Latte;

use Generator;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * Compiled form of a paired component tag such as `{rvtAlert}…{/rvtAlert}`.
 *
 * Opens the component before the body is evaluated so nested components can reach it,
 * captures the body, then renders the component around it.
 */
final class RivetBlockNode extends StatementNode
{
    public string $component;

    public ArrayNode $arguments;

    public AreaNode $content;

    /**
     * @return Generator<int, ?list<string>, array{AreaNode, ?Tag}, static>
     */
    public static function create(Tag $tag, string $component): Generator
    {
        // Assigned before the yield: Latte links the tag into the ancestor chain at this
        // point, and a node still null here would be invisible to its own descendants.
        $node = $tag->node = new static();
        $node->component = $component;
        $node->arguments = $tag->parser->parseArguments();

        [$node->content] = yield;

        return $node;
    }

    public function print(PrintContext $context): string
    {
        $id = $context->generateId();

        return $context->format(
            <<<'XX'
                $ʟ_rvtF%raw = $this->global->rivet->open(%dump, %node) %line;
                try {
                    ob_start(fn() => '');
                    try {
                        %node
                    } finally {
                        $ʟ_rvtC%raw = ob_get_clean();
                    }
                } finally {
                    $this->global->rivet->close($ʟ_rvtF%raw);
                }
                echo $this->global->rivet->render($ʟ_rvtF%raw, $ʟ_rvtC%raw);

                XX,
            $id,
            $this->component,
            $this->arguments,
            $this->position,
            $this->content,
            $id,
            $id,
            $id,
            $id,
        );
    }

    public function &getIterator(): Generator
    {
        yield $this->arguments;
        yield $this->content;
    }
}
