<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Pipes;

use Closure;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Support\Pipeline\PipeInterface;
use Horizon\Support\Pipeline\Pipeline;

class RunRouteMiddleware implements PipeInterface
{
    public function __construct(
        protected ContainerContract $container,
    ) {}

    /**
     * @param  RequestContextContract  $payload
     * @param  Closure(RequestContextContract): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $middlewares = $payload->getRoute()?->middleware() ?? [];

        if (empty($middlewares)) {
            return $next($payload);
        }

        return new Pipeline($this->container)
            ->send($payload)
            ->through($middlewares)
            ->then($next);
    }
}
