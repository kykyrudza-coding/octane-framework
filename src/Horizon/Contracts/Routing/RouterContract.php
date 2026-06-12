<?php

declare(strict_types=1);

namespace Horizon\Contracts\Routing;

interface RouterContract
{
    public function add(RouteDtoContract $route): RouteDtoContract;

    public function match(string $method, string $uri): ?RouteMatchContract;

    public function setFallback(RouteDtoContract $route): void;
}
