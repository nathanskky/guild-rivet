<?php

declare(strict_types=1);

/**
 * Print every registered component with its parameters.
 *
 * Reflects over the registry rather than restating it, so the output cannot drift from
 * the code the way a hand-written table does.
 */

use Guild\Rivet\Latte\RivetExtension;
use Guild\Rivet\Rivet;

require dirname(__DIR__) . '/vendor/autoload.php';

$registry = Rivet::registry();
$names = $registry->names();
sort($names);

printf("%-24s %-26s %-6s %s\n", 'TWIG', 'LATTE', 'BODY', 'PARAMETERS');
printf("%s\n", str_repeat('-', 100));

foreach ($names as $name) {
    $componentClass = $registry->classFor($name);
    $parameters = [];

    foreach (new ReflectionClass($componentClass)->getConstructor()?->getParameters() ?? [] as $parameter) {
        if ($parameter->getName() === 'extra') {
            continue;
        }

        $type = str_replace(['?', 'Guild\Rivet\Enum\\'], '', (string) $parameter->getType());
        $parameters[] = $parameter->getName() . ': ' . $type;
    }

    printf(
        "%-24s %-26s %-6s %s\n",
        $name,
        '{' . RivetExtension::tagName($name) . '}',
        $componentClass::acceptsContent() ? 'block' : 'leaf',
        implode(', ', $parameters),
    );
}

printf("\n%d components.\n", count($names));
