<?php

declare(strict_types=1);

namespace Horizon\Console;

use Horizon\Console\Output\OutputStyle;
use Horizon\Contracts\Console\CommandContract;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;

abstract class Command implements CommandContract
{
    public const SUCCESS = 0;
    public const FAILURE = 1;

    protected OutputStyle $style;

    final public function run(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $this->style = new OutputStyle($output);

        return $this->handle($input, $output);
    }

    abstract public function name(): string;

    public function description(): string
    {
        return '';
    }

    abstract public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int;
}


