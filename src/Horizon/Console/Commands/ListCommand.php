<?php

declare(strict_types=1);

namespace Horizon\Console\Commands;

use Horizon\Console\Command;
use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;

final class ListCommand extends Command
{
    public function __construct(
        private readonly CommandRegistryContract $commands,
    ) {}

    public function name(): string
    {
        return 'list';
    }

    public function description(): string
    {
        return 'List available commands.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output
    ): int {
        foreach ($this->commands->all() as $name => $command) {
            $instance = app()->make($command);

            $output->line($name.' - '.$instance->description());
        }

        return 0;
    }
}
