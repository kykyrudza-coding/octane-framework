<?php

declare(strict_types=1);

namespace Horizon\Contracts\Routing;

interface RouteCollectionContract
{
    public function add(RouteDtoContract $route): void;

    public function match(string $method, string $uri): ?RouteMatchContract;

    /**
     * @return list<RouteDtoContract>
     */
    public function all(): array;

    public function getByName(string $name): ?RouteDtoContract;

    public function setFallback(RouteDtoContract $route): void;
}
