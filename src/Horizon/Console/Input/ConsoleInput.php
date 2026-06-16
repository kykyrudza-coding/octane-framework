<?php

declare(strict_types=1);

namespace Horizon\Console\Input;

use Horizon\Contracts\Console\ConsoleInputContract;

final readonly class ConsoleInput implements ConsoleInputContract
{
    public function __construct(
        private array $argv,
    ) {}

    public function argument(int $index, mixed $default = null): mixed
    {
        return $this->argv[$index] ?? $default;
    }

    public function arguments(): array
    {
        return $this->argv;
    }
}
