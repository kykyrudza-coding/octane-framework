<?php

declare(strict_types=1);

namespace Horizon\Contracts\Routing;

interface PendingRouteContract
{
    public function name(string $name): PendingRouteContract;

    /**
     * @param  list<string>  $middleware
     */
    public function middleware(array $middleware): PendingRouteContract;

    public function fallback(): PendingRouteContract;

    public function register(): RouteDtoContract;
}
