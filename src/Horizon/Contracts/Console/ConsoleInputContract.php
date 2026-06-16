<?php

declare(strict_types=1);

namespace Horizon\Contracts\Console;

interface ConsoleInputContract
{
    public function argument(int $index, mixed $default = null);

    public function arguments(): array;
}
