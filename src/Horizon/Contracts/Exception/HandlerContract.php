<?php

declare(strict_types=1);

namespace Horizon\Contracts\Exception;

use Throwable;

interface HandlerContract
{
    public function register(): void;

    public function report(Throwable $exception): void;

    public function render(Throwable $exception): string;

    public function handle(Throwable $exception): void;
}
