<?php

declare(strict_types=1);

namespace Horizon\Console;

use Horizon\Console\Output\OutputStyle;
use Horizon\Contracts\Console\CommandContract;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;

abstract class Command implements CommandContract
{
    public const int SUCCESS = 0;
    public const int FAILURE = 1;

    protected OutputStyle $style;

    final public function run(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $this->style = new OutputStyle($output);

        return $this->handle($input, $output);
    }

    abstract public static function commandName(): string;
    public function name(): string
    {
        return static::commandName();
    }

    public function description(): string
    {
        return '';
    }

    abstract public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int;
}


