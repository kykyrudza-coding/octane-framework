<?php

declare(strict_types=1);

namespace Horizon\Console;

use Horizon\Console\Input\ConsoleInput;
use Horizon\Console\Output\ConsoleOutput;
use Horizon\Console\Output\OutputFormatter;
use Horizon\Contracts\Console\ConsoleKernelContract;
use Horizon\Contracts\Console\CommandRegistryContract;

final readonly class ConsoleKernel implements ConsoleKernelContract
{
    public function __construct(
        private CommandRegistryContract $commands,
    ) {}

    public function handle(array $argv): int
    {
        $input = new ConsoleInput($argv);
        $output = new ConsoleOutput(new OutputFormatter());

        $name = $input->argument(1, 'list');

        $command = $this->commands->find($name);

        if ($command === null) {
            $output->error("Command [$name] not found.");

            return 1;
        }

        return $command->run($input, $output);
    }
}
