<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Prism\Component;

interface ComponentRegistryContract
{
    public function register(ComponentContract $component): void;

    public function get(string $name): ?ComponentContract;

    public function has(string $name): bool;
}
