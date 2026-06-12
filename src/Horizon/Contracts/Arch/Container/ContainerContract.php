<?php

declare(strict_types=1);

namespace Horizon\Contracts\Arch\Container;

interface ContainerContract
{
    public function bind(string $abstract, callable|string $concrete): void;

    public function bindPath(string $abstract, string $path): void;

    public function bindAlias(string $alias, string $abstract): void;

    public function singleton(string $abstract, callable|string $concrete): void;

    public function instance(string $abstract, object $instance): void;

    public function make(string $abstract): mixed;

    public function has(string $abstract): bool;
}
