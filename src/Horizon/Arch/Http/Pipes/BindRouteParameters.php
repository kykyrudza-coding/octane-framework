<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Pipes;

use Closure;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Support\Pipeline\PipeInterface;

class BindRouteParameters implements PipeInterface
{
    /**
     * @param  RequestContextContract  $payload
     * @param  Closure(RequestContextContract): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        return $next($payload);
    }
}
