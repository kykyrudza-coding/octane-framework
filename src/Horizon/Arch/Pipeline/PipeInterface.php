<?php

declare(strict_types=1);

namespace Horizon\Arch\Pipeline;

use Closure;

interface PipeInterface
{
    public function handle(mixed $payload, Closure $next): mixed;
}
