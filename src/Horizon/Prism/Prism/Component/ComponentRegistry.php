<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Component;

use Horizon\Contracts\Prism\Component\ComponentContract;
use Horizon\Contracts\Prism\Component\ComponentRegistryContract;
use InvalidArgumentException;

final class ComponentRegistry implements ComponentRegistryContract
{
    /** @var array<string, ComponentContract> */
    private array $components = [];

    public function register(ComponentContract $component): void
    {
        if (!method_exists($component, 'name')) {
            throw new InvalidArgumentException(
                'Component must implement a name() method to be registered.'
            );
        }

        /** @phpstan-ignore-next-line */
        $this->components[$component->name()] = $component;
    }

    /**
     * Register a component class by alias name.
     * This allows mapping alias => concrete class without instantiating.
     */
    public function registerAlias(string $alias, string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Component class '$class' does not exist.");
        }

        $this->components[$alias] = new $class();
    }

    public function get(string $name): ?ComponentContract
    {
        return $this->components[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->components[$name]);
    }
}
