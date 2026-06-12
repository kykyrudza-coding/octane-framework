<?php

declare(strict_types=1);

namespace Horizon\Routing;

use Closure;
use Horizon\Contracts\Routing\RouterContract;
use Horizon\Contracts\Routing\RouteRegistrarContract;

class RouteRegistrar implements RouteRegistrarContract
{
    /**
     * @var array{prefix?: string, middleware?: list<string>, name?: string, group?: string}
     */
    private array $groupStack = [];

    /**
     * @var array{prefix?: string, middleware?: list<string>, name?: string, group?: string}|null
     */
    private ?array $pendingGroupBaseStack = null;

    public function __construct(
        private readonly RouterContract $router,
    ) {}

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function get(string $uri, Closure|array|string $action): PendingRoute
    {
        return $this->createPendingRoute('GET', $uri, $action);
    }

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function post(string $uri, Closure|array|string $action): PendingRoute
    {
        return $this->createPendingRoute('POST', $uri, $action);
    }

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function put(string $uri, Closure|array|string $action): PendingRoute
    {
        return $this->createPendingRoute('PUT', $uri, $action);
    }

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function patch(string $uri, Closure|array|string $action): PendingRoute
    {
        return $this->createPendingRoute('PATCH', $uri, $action);
    }

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function delete(string $uri, Closure|array|string $action): PendingRoute
    {
        return $this->createPendingRoute('DELETE', $uri, $action);
    }

    public function prefix(string $prefix): static
    {
        $this->rememberGroupBaseStack();

        $existing = $this->groupStack['prefix'] ?? '';
        $newPrefix = trim($existing, '/') . '/' . trim($prefix, '/');
        $this->groupStack['prefix'] = trim($newPrefix, '/');

        return $this;
    }

    /**
     * @param  list<string>  $middleware
     */
    public function middleware(array $middleware): static
    {
        $this->rememberGroupBaseStack();

        $existing = $this->groupStack['middleware'] ?? [];
        $this->groupStack['middleware'] = array_merge($existing, $middleware);

        return $this;
    }

    public function name(string $name): static
    {
        $this->rememberGroupBaseStack();

        $existing = $this->groupStack['name'] ?? '';
        $this->groupStack['name'] = $existing.$name;

        return $this;
    }

    /**
     * @param  Closure(self): void  $callback
     */
    public function group(Closure $callback): void
    {
        $previousStack = $this->pendingGroupBaseStack ?? $this->groupStack;
        $this->pendingGroupBaseStack = null;

        $callback($this);

        $this->groupStack = $previousStack;
    }

    /**
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function fallback(Closure|array|string $action): PendingRoute
    {
        return $this->createPendingRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/{fallback}', $action)->fallback();
    }

    /**
     * @param  string|list<string>  $method
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     */
    public function createPendingRoute(
        string|array $method,
        string $uri,
        Closure|array|string $action
    ): PendingRoute {
        $prefix = $this->groupStack['prefix'] ?? null;
        $middleware = $this->groupStack['middleware'] ?? [];
        $namePrefix = $this->groupStack['name'] ?? null;
        $routeGroup = $this->groupStack['group'] ?? null;

        $routeMethod = is_array($method) ? $method : [$method];
        $this->pendingGroupBaseStack = null;

        $pending = new PendingRoute(
            router: $this->router,
            methods: $routeMethod,
            uri: $uri,
            action: $action,
            prefix: $prefix,
            namePrefix: $namePrefix,
            routeGroup: $routeGroup
        );

        if (! empty($middleware)) {
            $pending->middleware($middleware);
        }

        return $pending;
    }

    public function setCurrentGroup(string $group): void
    {
        $this->groupStack['group'] = $group;
    }

    public function clearCurrentGroup(): void
    {
        unset($this->groupStack['group']);
    }

    private function rememberGroupBaseStack(): void
    {
        if ($this->pendingGroupBaseStack === null) {
            $this->pendingGroupBaseStack = $this->groupStack;
        }
    }
}
