<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Pipes;

use Closure;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Http\Middleware\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Support\Pipeline\PipeInterface;
use Horizon\Support\Pipeline\Pipeline;

class RunGlobalMiddleware implements PipeInterface
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
        $middlewares = $this->middlewareCollection->getGlobal();

        if (empty($middlewares)) {
            return $next($payload);
        }

        return new Pipeline($this->container)
            ->send($payload)
            ->through($middlewares)
            ->then($next);
    }
}
