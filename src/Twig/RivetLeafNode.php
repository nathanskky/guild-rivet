<?php

declare(strict_types=1);

namespace Guild\Rivet\Twig;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

/**
 * Compiled form of a component that takes no body, such as `{% rvt_badge %}`.
 */
#[YieldReady]
final class RivetLeafNode extends Node
{
    /** Expression that resolves the runtime inside a compiled template. */
    private const string RUNTIME = '$this->env->getRuntime(\\' . RivetRuntime::class . '::class)';

    public function __construct(string $component, AbstractExpression $arguments, int $lineno)
    {
        parent::__construct(['arguments' => $arguments], ['component' => $component], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write('yield ' . self::RUNTIME . '->leaf(')
            ->repr($this->getAttribute('component'))
            ->raw(', ')
            ->subcompile($this->getNode('arguments'))
            ->raw(");\n");
    }
}
