<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Pipes;

use Closure;
use Horizon\Contracts\Arch\ContainerContract;
use Horizon\Contracts\Http\Collection\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Support\Pipeline\PipeInterface;
use Horizon\Support\Pipeline\Pipeline;

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
