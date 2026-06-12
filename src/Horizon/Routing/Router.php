<?php

declare(strict_types=1);

namespace Horizon\Routing;

use Horizon\Contracts\Routing\RouteCollectionContract;
use Horizon\Contracts\Routing\RouteDtoContract;
use Horizon\Contracts\Routing\RouteMatchContract;
use Horizon\Contracts\Routing\RouterContract;

final readonly class Router implements RouterContract
{
    public function __construct(
        private RouteCollectionContract $routes,
    ) {}

    public function add(RouteDtoContract $route): RouteDtoContract
    {
        $this->routes->add($route);

        return $route;
    }

    public function match(string $method, string $uri): ?RouteMatchContract
    {
        return $this->routes->match($method, $uri);
    }

    public function setFallback(RouteDtoContract $route): void
    {
        $this->routes->setFallback($route);
    }
}
