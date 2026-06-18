<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Contracts\Exception\HandlerContract;
use Horizon\Contracts\Http\Collection\MiddlewareCollectionContract;
use Horizon\Contracts\Routing\RouteRegistrarContract;
use Horizon\Support\Pipeline\PipeInterface;

class ApplyBuilderCallbacks implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $this->route($payload);
        $this->middleware($payload);
        $this->exception($payload);

        return $next($payload);
    }

    protected function route(ApplicationBuilder $payload): void
    {
        $registrar = $payload->app->make(RouteRegistrarContract::class);
        if (! $registrar instanceof RouteRegistrarContract) {
            throw new BindingResolutionException('Route registrar binding must resolve to a RouteRegistrarContract instance.');
        }

        foreach ($payload->getWebRoutes() as $routeFile) {
            if (! is_file($routeFile)) {
                continue;
            }

            $registrar->setCurrentGroup('web');
            require $routeFile;
        }

        foreach ($payload->getApiRoutes() as $routeFile) {
            if (! is_file($routeFile)) {
                continue;
            }

            $registrar->setCurrentGroup('api');
            require $routeFile;
        }

        $registrar->clearCurrentGroup();
    }

    protected function middleware(ApplicationBuilder $payload): void
    {
        $collection = $payload->app->make(MiddlewareCollectionContract::class);
        if (! $collection instanceof MiddlewareCollectionContract) {
            throw new BindingResolutionException('Middleware collection binding must resolve to a MiddlewareCollectionContract instance.');
        }

        if ($callback = $payload->getMiddleware()) {
            $callback($collection);
        }

        $payload->app->instance(
            MiddlewareCollectionContract::class,
            $collection
        );
    }

    protected function exception(ApplicationBuilder $payload): void
    {
        if ($payload->getExceptions() instanceof Closure) {
            $handler = $payload->app->make(HandlerContract::class);
            if (! $handler instanceof HandlerContract) {
                throw new BindingResolutionException('Exception handler binding must resolve to a HandlerContract instance.');
            }

            ($payload->getExceptions())(
                $handler
            );
        }
    }
}
