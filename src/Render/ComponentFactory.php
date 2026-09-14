<?php

declare(strict_types=1);

namespace Guild\Rivet\Render;

use BackedEnum;
use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Builds a component from the named arguments written in a template.
 *
 * Both engine integrations hand over a plain `name => value` map, so the same rules
 * apply on either side: arguments matching a constructor parameter are passed to it,
 * strings are resolved into backed enums because a template cannot write a PHP enum
 * case, and anything left over becomes an HTML attribute. Mistakes are reported against
 * the component's template-facing name rather than its PHP class, since that is what the
 * developer wrote.
 */
final class ComponentFactory
{
    /**
     * @param  class-string<Component>  $componentClass
     * @param  array<array-key, mixed>  $arguments  named, or positional by integer key
     *
     * @throws InvalidArgumentException
     */
    public function create(string $componentClass, array $arguments): Component
    {
        $parameters = $this->parametersOf($componentClass);
        $arguments = $this->nameByPosition($componentClass, $parameters, $arguments);

        /** @var array<string, mixed> $named */
        $named = [];
        /** @var array<string, string|bool|int|float|null> $extra */
        $extra = [];

        foreach ($arguments as $key => $value) {
            if ($key !== 'extra' && array_key_exists($key, $parameters)) {
                $named[$key] = $this->coerce($componentClass, $parameters[$key], $value);

                continue;
            }

            if ($value !== null && ! is_scalar($value)) {
                throw new InvalidArgumentException(sprintf(
                    'The "%s" attribute on %s must be a scalar value, %s given.',
                    $key,
                    $componentClass::name(),
                    get_debug_type($value),
                ));
            }

            $extra[$key] = $value;
        }

        foreach ($parameters as $name => $parameter) {
            if (! $parameter->isOptional() && ! array_key_exists($name, $named)) {
                throw new InvalidArgumentException(sprintf(
                    '%s requires the "%s" argument.',
                    $componentClass::name(),
                    $name,
                ));
            }
        }

        if ($extra !== [] && array_key_exists('extra', $parameters)) {
            $named['extra'] = new Attributes($extra);
        }

        return new $componentClass(...$named);
    }

    /**
     * Resolve integer-keyed arguments to parameter names, so a template can pass the
     * obvious first argument without naming it.
     *
     * @param  class-string<Component>  $componentClass
     * @param  array<string, ReflectionParameter>  $parameters
     * @param  array<array-key, mixed>  $arguments
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    private function nameByPosition(string $componentClass, array $parameters, array $arguments): array
    {
        $order = array_keys($parameters);
        $named = [];

        foreach ($arguments as $key => $value) {
            if (! is_int($key)) {
                $named[$key] = $value;

                continue;
            }

            $name = $order[$key] ?? throw new InvalidArgumentException(sprintf(
                '%s accepts %d positional arguments, %d given.',
                $componentClass::name(),
                count($order),
                $key + 1,
            ));

            if (array_key_exists($name, $arguments)) {
                throw new InvalidArgumentException(sprintf(
                    '%s received the "%s" argument both by position and by name.',
                    $componentClass::name(),
                    $name,
                ));
            }

            $named[$name] = $value;
        }

        return $named;
    }

    /**
     * @param  class-string<Component>  $componentClass
     * @return array<string, ReflectionParameter>
     */
    private function parametersOf(string $componentClass): array
    {
        $constructor = new ReflectionClass($componentClass)->getConstructor();
        $parameters = [];

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        return $parameters;
    }

    /**
     * Resolve a template string into a backed enum case where the parameter expects one.
     *
     * @param  class-string<Component>  $componentClass
     *
     * @throws InvalidArgumentException
     */
    private function coerce(string $componentClass, ReflectionParameter $parameter, mixed $value): mixed
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin() || ! is_string($value)) {
            return $value;
        }

        $enum = $type->getName();

        if (! is_subclass_of($enum, BackedEnum::class)) {
            return $value;
        }

        $case = $enum::tryFrom($value);

        if ($case === null) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a valid %s for %s. Expected one of: %s.',
                $value,
                $parameter->getName(),
                $componentClass::name(),
                implode(', ', array_map(
                    static fn (BackedEnum $case): string => (string) $case->value,
                    $enum::cases(),
                )),
            ));
        }

        return $case;
    }
}
