<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Handle;

use Closure;
use Horizon\Arch\Pipeline\PipeInterface;
use Horizon\Arch\Pipeline\Pipeline;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Http\Middleware\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Request\RequestContextContract;

class RunGroupMiddleware implements PipeInterface
{
    public function __construct(
        protected ContainerContract $container,
        protected MiddlewareCollectionContract $middlewareCollection,
    ) {}

    /**
     * @param  RequestContextContract  $payload
     * @param  Closure(RequestContextContract): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $group = $payload->getRoute()?->routeGroup();

        if ($group === null) {
            return $next($payload);
        }

        $middlewares = $this->middlewareCollection->getGroup($group);

        if (empty($middlewares)) {
            return $next($payload);
        }

        return new Pipeline($this->container)
            ->send($payload)
            ->through($middlewares)
            ->then($next);
    }
}
