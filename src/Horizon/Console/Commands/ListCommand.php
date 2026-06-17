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

    public static function commandName(): string
    {
        return 'list';
    }

    public function description(): string
    {
        return 'List available commands.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $this->style->title('Octane Framework');
        $this->style->section('Available commands');

        foreach ($this->commands->all() as $name => $class) {
            $instance = $this->commands->find($name);

            $this->style->keyValue(
                $name,
                $instance?->description() ?? '',
            );
        }

        $this->style->newLine();

        return self::SUCCESS;
    }
}
