<?php

declare(strict_types=1);

namespace Horizon\Routing;

use Closure;
use Horizon\Contracts\Routing\RouteDtoContract;

final readonly class RouteDTO implements RouteDtoContract
{
    /**
     * @param  list<string>  $methods
     * @param  Closure|array{0: class-string, 1: string}|string  $action
     * @param  list<string>  $middleware
     */
    public function __construct(
        public array $methods,
        public string $uri,
        public Closure|array|string $action,
        public array $middleware = [],
        public ?string $name = null,
        public ?string $prefix = null,
        public ?string $routeGroup = null,
    ) {}

    /**
     * @return list<string>
     */
    public function methods(): array
    {
        return $this->methods;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * @return Closure|array{0: class-string, 1: string}|string
     */
    public function action(): Closure|array|string
    {
        return $this->action;
    }

    /**
     * @return list<string>
     */
    public function middleware(): array
    {
        return $this->middleware;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function prefix(): ?string
    {
        return $this->prefix;
    }

    public function routeGroup(): ?string
    {
        return $this->routeGroup;
    }
}
