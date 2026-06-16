<?php

declare(strict_types=1);

namespace Horizon\Contracts\Console;

interface ConsoleKernelContract
{
    public function handle(array $argv): int;
}
