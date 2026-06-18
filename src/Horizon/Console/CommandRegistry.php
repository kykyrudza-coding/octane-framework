<?php

declare(strict_types=1);

namespace Horizon\Console;

use Horizon\Contracts\Arch\ContainerContract;
use Horizon\Contracts\Console\CommandContract;
use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Console\Exceptions\InvalidCommandException;
use ReflectionClass;
use ReflectionException;

final class CommandRegistry implements CommandRegistryContract
{
    /**
     * @var array<string, class-string<CommandContract>>
     */
    private array $commands = [];

    public function __construct(
        private readonly ContainerContract $container,
    ) {}

    public function register(string $command): void
    {
        if (! is_a($command, CommandContract::class, true)) {
            throw new InvalidCommandException("Console command [$command] must implement CommandContract.");
        }

        $this->commands[$command::commandName()] = $command;
    }

    public function find(?string $name): ?CommandContract
    {
        if ($name === null) {
            return null;
        }

        $command = $this->commands[$name] ?? null;

        if ($command === null) {
            return null;
        }

        $instance = $this->container->make($command);

        if (! $instance instanceof CommandContract) {
            throw new InvalidCommandException("Console command [$command] must implement CommandContract.");
        }

        return $instance;
    }

    public function all(): array
    {
        return $this->commands;
    }
}
