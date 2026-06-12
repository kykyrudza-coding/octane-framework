<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Compiler;

interface DirectiveRegistryContract
{
    public function register(DirectiveContract|callable $directive, ?string $name = null): void;

    public function get(string $name): ?DirectiveContract;

    public function has(string $name): bool;

    /**
     * @return DirectiveContract[]
     */
    public function all(): array;
}
