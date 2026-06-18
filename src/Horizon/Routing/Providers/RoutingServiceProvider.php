<?php

declare(strict_types=1);

namespace Horizon\Routing\Providers;

use Horizon\Support\Providers\ServiceProvider;
use Horizon\Contracts\Routing\RouteCollectionContract;
use Horizon\Contracts\Routing\RouterContract;
use Horizon\Contracts\Routing\RouteRegistrarContract;
use Horizon\Routing\Exceptions\RouteBindingException;
use Horizon\Routing\RouteCollection;
use Horizon\Routing\Router;
use Horizon\Routing\RouteRegistrar;

class RoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            RouteCollectionContract::class,
            fn () => new RouteCollection
        );

        $this->app->singleton(
            RouterContract::class,
            function () {
                $routes = $this->app->make(RouteCollectionContract::class);
                if (! $routes instanceof RouteCollectionContract) {
                    throw new RouteBindingException('Route collection binding must resolve to a RouteCollectionContract instance.');
                }

                return new Router($routes);
            }
        );

        $this->app->singleton(
            RouteRegistrarContract::class,
            function () {
                $router = $this->app->make(RouterContract::class);
                if (! $router instanceof RouterContract) {
                    throw new RouteBindingException('Router binding must resolve to a RouterContract instance.');
                }

                return new RouteRegistrar($router);
            }
        );
    }

    public function boot(): void
    {
        $registrar = $this->app->make(RouteRegistrarContract::class);
        if (! $registrar instanceof RouteRegistrarContract) {
            throw new RouteBindingException('Route registrar binding must resolve to a RouteRegistrarContract instance.');
        }
    }
}
