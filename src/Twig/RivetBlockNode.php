<?php

declare(strict_types=1);

namespace Guild\Rivet\Twig;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

/**
 * Compiled form of a paired component tag such as `{% rvt_alert %}…{% endrvt_alert %}`.
 *
 * Opens the component before the body is evaluated, so anything nested inside can reach
 * it, captures the body as a string, then renders the component around it.
 */
#[YieldReady]
final class RivetBlockNode extends Node
{
    /** Expression that resolves the runtime inside a compiled template. */
    private const string RUNTIME = '$this->env->getRuntime(\\' . RivetRuntime::class . '::class)';

    public function __construct(string $component, AbstractExpression $arguments, Node $body, int $lineno)
    {
        parent::__construct(
            ['arguments' => $arguments, 'body' => $body],
            ['component' => $component],
            $lineno,
        );
    }

    public function compile(Compiler $compiler): void
    {
        $runtime = self::RUNTIME;
        $frame = '$_rvtFrame' . $compiler->getVarName();
        $body = '$_rvtBody' . $compiler->getVarName();

        $compiler->addDebugInfo($this);

        $compiler
            ->write($frame . ' = ' . $runtime . '->open(')
            ->repr($this->getAttribute('component'))
            ->raw(', ')
            ->subcompile($this->getNode('arguments'))
            ->raw(");\n");

        // The pop must happen even if the body throws: the runtime outlives this render,
        // so a leaked frame would corrupt every later one in the same process.
        $compiler->write("try {\n")->indent();
        $this->compileBodyCapture($compiler, $body);
        $compiler
            ->outdent()
            ->write("} finally {\n")
            ->indent()
            ->write($runtime . '->close(' . $frame . ");\n")
            ->outdent()
            ->write("}\n");

        $compiler
            ->write('yield ' . $runtime . '->render(' . $frame . ', ' . $body . ");\n");
    }

    /**
     * Capture the tag body into a string.
     *
     * `doDisplay()` is always a generator in Twig 3, but with `use_yield` off a legacy
     * node may still echo into the output buffer, so the two cases need different
     * collection. The empty `yield from` keeps the closure a generator even when the
     * body compiles to nothing.
     */
    private function compileBodyCapture(Compiler $compiler, string $target): void
    {
        $useYield = $compiler->getEnvironment()->useYield();

        $compiler
            ->write($target . ' = ')
            ->raw($useYield
                ? "implode('', iterator_to_array("
                : '\Twig\Extension\CoreExtension::captureOutput(')
            ->raw("(function () use (&\$context, \$macros, \$blocks) {\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->write("yield from [];\n")
            ->outdent()
            ->write('})()')
            ->raw($useYield ? ", false));\n" : ");\n");
    }
}
