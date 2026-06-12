<?php

declare(strict_types=1);

namespace Horizon\Contracts\Routing;

use Closure;

interface RouteRegistrarContract
{
    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function get(string $uri, Closure|array|string $action): PendingRouteContract;

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function post(string $uri, Closure|array|string $action): PendingRouteContract;

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function put(string $uri, Closure|array|string $action): PendingRouteContract;

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function patch(string $uri, Closure|array|string $action): PendingRouteContract;

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function delete(string $uri, Closure|array|string $action): PendingRouteContract;

    public function prefix(string $prefix): RouteRegistrarContract;

    /**
     * @param  list<string>  $middleware
     */
    public function middleware(array $middleware): RouteRegistrarContract;

    public function name(string $name): RouteRegistrarContract;

    /**
     * @param  Closure(self): void  $callback
     */
    public function group(Closure $callback): void;

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function fallback(Closure|array|string $action): PendingRouteContract;

    /**
     * @param  string|list<string>  $method
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function createPendingRoute(string|array $method, string $uri, Closure|array|string $action): PendingRouteContract;

    public function setCurrentGroup(string $group): void;

    public function clearCurrentGroup(): void;
}
