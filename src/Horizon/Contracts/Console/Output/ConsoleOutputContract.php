<?php

declare(strict_types=1);

namespace Horizon\Contracts\Console\Output;

interface ConsoleOutputContract
{
    public function write(string $message): void;

    public function line(string $message = ''): void;

    public function error(string $message): void;

    public function newLine(int $count = 1): void;
}
