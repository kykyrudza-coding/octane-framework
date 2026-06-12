<?php

declare(strict_types=1);

namespace Horizon\Routing;

use Closure;
use Horizon\Contracts\Routing\PendingRouteContract;
use Horizon\Contracts\Routing\RouteDtoContract;
use Horizon\Contracts\Routing\RouterContract;

final class PendingRoute implements PendingRouteContract
{
    /**
     * @var list<string>
     */
    private array $middleware = [];

    private ?string $name = null;

    private bool $isFallback = false;

    private bool $registered = false;

    /**
     * @param  list<string>|string  $methods
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function __construct(
        private readonly RouterContract $router,
        private readonly string|array $methods,
        private readonly string $uri,
        private readonly Closure|array|string $action,
        private readonly ?string $prefix = null,
        private readonly ?string $namePrefix = null,
        private readonly ?string $routeGroup = null,
    ) {}

    public function name(string $name): PendingRoute
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  list<string>  $middleware
     */
    public function middleware(array $middleware): PendingRoute
    {
        $this->middleware = $middleware;

        return $this;
    }

    public function fallback(): PendingRoute
    {
        $this->isFallback = true;

        return $this;
    }

    public function register(): RouteDtoContract
    {
        if ($this->registered) {
            throw new \LogicException('Route has already been registered.');
        }

        $this->registered = true;

        $parts = [];
        if ($this->prefix) {
            $parts[] = trim($this->prefix, '/');
        }
        if ($this->uri && $this->uri !== '/') {
            $parts[] = trim($this->uri, '/');
        }
        $uri = '/' . implode('/', $parts);

        $route = new RouteDTO(
            methods: array_map(static fn (string $method): string => strtoupper($method), (array) $this->methods),
            uri: $uri,
            action: $this->action,
            middleware: $this->middleware,
            name: $this->name === null ? null : ($this->namePrefix ?? '').$this->name,
            prefix: $this->prefix,
            routeGroup: $this->routeGroup,
        );

        if ($this->isFallback) {
            $this->router->setFallback($route);
        } else {
            $this->router->add($route);
        }

        return $route;
    }

    public function __destruct()
    {
        // Route files rely on this DSL contract: Route::get(...)->name(...)
        // registers when the pending route leaves scope.
        if (! $this->registered) {
            $this->register();
        }
    }
}
