<?php

declare(strict_types=1);

namespace Horizon\Contracts\Http\Middleware;

use Closure;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Response\ResponseContract;

interface MiddlewareContract
{
    public function handle(RequestContextContract $context, Closure $next): ResponseContract;
}
