<?php

declare(strict_types=1);

namespace Horizon\Support\Facades;

use Closure;
use Horizon\Contracts\Routing\PendingRouteContract;
use Horizon\Contracts\Routing\RouteRegistrarContract;

/**
 * @method static PendingRouteContract get(string $uri, Closure|array|string $action)
 * @method static PendingRouteContract post(string $uri, Closure|array|string $action)
 * @method static PendingRouteContract put(string $uri, Closure|array|string $action)
 * @method static PendingRouteContract patch(string $uri, Closure|array|string $action)
 * @method static PendingRouteContract delete(string $uri, Closure|array|string $action)
 * @method static RouteRegistrarContract prefix(string $prefix)
 * @method static RouteRegistrarContract middleware(array $middleware)
 * @method static RouteRegistrarContract name(string $name)
 * @method static void group(Closure $callback)
 * @method static PendingRouteContract fallback(Closure|array|string $action)
 *
 *
 * @see \Horizon\Routing\RouteRegistrar
 */
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RouteRegistrarContract::class;
    }
}
