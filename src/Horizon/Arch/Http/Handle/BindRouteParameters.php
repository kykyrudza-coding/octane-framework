<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Handle;

use Closure;
use Horizon\Arch\Pipeline\PipeInterface;
use Horizon\Contracts\Http\Request\RequestContextContract;

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
