<?php

declare(strict_types=1);

namespace Horizon\Arch\Http;

use Horizon\Arch\Http\Pipes\BindRouteParameters;
use Horizon\Arch\Http\Pipes\InvokeController;
use Horizon\Arch\Http\Pipes\ResolveRoute;
use Horizon\Arch\Http\Pipes\RunGlobalMiddleware;
use Horizon\Arch\Http\Pipes\RunGroupMiddleware;
use Horizon\Arch\Http\Pipes\RunRouteMiddleware;
use Horizon\Contracts\Arch\Application\ApplicationContract;
use Horizon\Contracts\Http\HttpKernel\HttpKernelContract;
use Horizon\Contracts\Http\Middleware\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Support\Pipeline\Pipeline;
use RuntimeException;

class HttpKernel implements HttpKernelContract
{
    protected float $requestStartTime;

    public function __construct(
        protected ApplicationContract $app,
    ) {
        $this->requestStartTime = microtime(true);
    }

    public function handle(RequestContextContract $requestContext): ResponseContract
    {
        $response = new Pipeline($this->app->getContainer())
            ->send($requestContext)
            ->through([
                RunGlobalMiddleware::class,     /** Run global middleware */
                ResolveRoute::class,            /** Resolve route and controller */
                BindRouteParameters::class,     /** Bind route parameters */
                RunGroupMiddleware::class,      /** Run route group middleware */
                RunRouteMiddleware::class,      /** Run route middleware */
                InvokeController::class,        /** Invoke controller */
            ])
            ->then(fn (RequestContextContract $context) => $context->getResponse());

        if (! $response instanceof ResponseContract) {
            throw new RuntimeException('HTTP pipeline must return a ResponseContract instance.');
        }

        return $response;
    }

    public function terminate(
        RequestContextContract $requestContext,
        ResponseContract $response
    ): void {
        $collection = $this->app->make(MiddlewareCollectionContract::class);
        if (! $collection instanceof MiddlewareCollectionContract) {
            throw new RuntimeException('Middleware collection binding must resolve to a MiddlewareCollectionContract instance.');
        }

        $middlewares = array_merge(
            $collection->getGlobal(),
            $collection->getGroup($requestContext->getRoute()?->routeGroup() ?? ''),
            $requestContext->getRoute()?->middleware() ?? []
        );

        foreach ($middlewares as $middleware) {
            $instance = $this->app->make($middleware);
            if (is_object($instance) && is_callable([$instance, 'terminate'])) {
                $instance->terminate($requestContext, $response);
            }
        }

        $this->app->terminate();
    }

    public function getApplication(): ApplicationContract
    {
        return $this->app;
    }
}
