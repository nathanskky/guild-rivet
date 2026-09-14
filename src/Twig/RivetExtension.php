<?php

declare(strict_types=1);

namespace Guild\Rivet\Twig;

use Guild\Rivet\Render\ComponentRegistry;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

/**
 * Makes every registered Rivet component available to Twig.
 *
 * Components appear as tags — paired where they take a body, single where they do not —
 * and those taking no body are additionally exposed as functions, which reads better
 * inline. Both forms compile to the same renderer call, so they cannot diverge.
 */
final class RivetExtension extends AbstractExtension
{
    public function __construct(
        private readonly ComponentRegistry $registry,
    ) {
    }

    public function getTokenParsers(): array
    {
        $parsers = [];

        foreach ($this->registry->names() as $name) {
            $parsers[] = new RivetTokenParser($name, $this->registry->classFor($name)::acceptsContent());
        }

        return $parsers;
    }

    public function getFunctions(): array
    {
        $functions = [];

        foreach ($this->registry->names() as $name) {
            if ($this->registry->classFor($name)::acceptsContent()) {
                continue;
            }

            $functions[] = new TwigFunction(
                $name,
                static function (Environment $environment, mixed ...$arguments) use ($name): Markup {
                    /** @var RivetRuntime $runtime */
                    $runtime = $environment->getRuntime(RivetRuntime::class);

                    return new Markup($runtime->leaf($name, $arguments), 'UTF-8');
                },
                // is_variadic collects unmatched named arguments instead of rejecting
                // them, which is what lets a caller pass arbitrary HTML attributes.
                // is_safe marks the result pre-escaped so Twig does not escape our markup.
                ['needs_environment' => true, 'is_variadic' => true, 'is_safe' => ['html']],
            );
        }

        return $functions;
    }
}
