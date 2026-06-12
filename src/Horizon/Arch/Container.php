<?php

declare(strict_types=1);

namespace Horizon\Arch;

use Horizon\Contracts\Arch\Container\ContainerContract;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

class Container implements ContainerContract
{
    /**
     * @var array<string, callable(self): mixed|string>
     */
    protected array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * @var array<string, true>
     */
    protected array $singletons = [];

    /**
     * @var array<string, true>
     */
    protected array $resolving = [];

    /**
     * @var array<string, string>
     */
    protected array $paths = [];

    /**
     * @var array<string, string>
     */
    protected array $aliases = [];

    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function bindPath(string $abstract, string $path): void
    {
        $this->paths[$abstract] = $path;
    }

    public function bindAlias(string $alias, string $abstract): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = true;
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * @throws ReflectionException
     */
    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->paths[$abstract])) {
            return $this->paths[$abstract];
        }

        if (isset($this->aliases[$abstract])) {
            return $this->make($this->aliases[$abstract]);
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        $instance = $this->resolve($concrete);

        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * @param  callable(self): mixed|string  $concrete
     *
     * @throws ReflectionException
     */
    protected function resolve(callable|string $concrete): mixed
    {
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        if (! class_exists($concrete)) {
            throw new RuntimeException("Class $concrete does not exist.");
        }

        $reflection = new ReflectionClass($concrete);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class $concrete is not instantiable.");
        }

        if (isset($this->resolving[$concrete])) {
            $chain = implode(' → ', array_keys($this->resolving));
            throw new RuntimeException(
                "Circular dependency detected: $chain → $concrete"
            );
        }

        $this->resolving[$concrete] = true;

        try {
            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                return new $concrete;
            }

            $dependencies = array_map(
                fn (ReflectionParameter $param) => $this->resolveDependency($param),
                $constructor->getParameters()
            );

            return $reflection->newInstanceArgs($dependencies);
        } finally {
            unset($this->resolving[$concrete]);
        }
    }

    /**
     * @throws ReflectionException
     */
    protected function resolveDependency(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        if ($type === null) {
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new RuntimeException(
                "Cannot resolve parameter \${$param->getName()} — no type hint."
            );
        }

        if (! $type instanceof ReflectionNamedType) {
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            if ($type->allowsNull()) {
                return null;
            }

            throw new RuntimeException(
                "Cannot resolve union/intersection type for parameter \${$param->getName()}."
            );
        }

        $typeName = $type->getName();

        if (! in_array($typeName, ['int', 'float', 'string', 'bool', 'array']) && $this->has($typeName)) {
            return $this->make($typeName);
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if ($type->allowsNull()) {
            return null;
        }

        if (in_array($typeName, ['int', 'float', 'string', 'bool', 'array'])) {
            throw new RuntimeException(
                "Cannot resolve primitive parameter \${$param->getName()} of type $typeName."
            );
        }

        return $this->make($typeName);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->paths[$abstract])
            || isset($this->aliases[$abstract]);
    }
}
