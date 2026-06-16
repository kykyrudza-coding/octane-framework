<?php

declare(strict_types=1);

namespace Horizon\Contracts\Console;

interface CommandRegistryContract
{
    /**
     * @param class-string<CommandContract> $command
     */
    public function register(string $command): void;

    public function find(?string $name): ?CommandContract;

    /**
     * @return array<string, class-string<CommandContract>>
     */
    public function all(): array;
}
