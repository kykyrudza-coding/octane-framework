<?php

declare(strict_types=1);

namespace Horizon\Contracts\Console;

interface CommandContract
{
    public function name(): string;

    public static function commandName(): string;

    public function description(): string;

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output
    ): int;
}
