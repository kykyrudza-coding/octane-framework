<?php

declare(strict_types=1);

namespace Horizon\Routing;

use Horizon\Contracts\Routing\RouteCollectionContract;
use Horizon\Contracts\Routing\RouteDtoContract;
use Horizon\Contracts\Routing\RouteMatchContract;

class RouteCollection implements RouteCollectionContract
{
    /**
     * @var list<RouteDtoContract>
     */
    private array $routes = [];

    private ?RouteDtoContract $fallbackRoute = null;

    public function add(RouteDtoContract $route): void
    {
        $this->routes[] = $route;
    }

    public function match(string $method, string $uri): ?RouteMatchContract
    {
        foreach ($this->routes as $route) {
            if (! in_array(strtoupper($method), $route->methods(), true)) {
                continue;
            }

            $params = $this->matchUri($route->uri(), $uri);

            if ($params !== null) {
                return new RouteMatch($route, $params);
            }
        }

        if ($this->fallbackRoute !== null) {
            return new RouteMatch($this->fallbackRoute, []);
        }

        return null;
    }

    /**
     * @return list<RouteDtoContract>
     */
    public function all(): array
    {
        return $this->routes;
    }

    public function getByName(string $name): ?RouteDtoContract
    {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                return $route;
            }
        }

        return null;
    }

    public function setFallback(RouteDtoContract $route): void
    {
        $this->fallbackRoute = $route;
    }

    /**
     * @return array<string, string>|null
     */
    private function matchUri(string $uri, string $requestUri): ?array
    {
        $uri = '/' . trim($uri, '/');
        $requestUri = '/' . trim($requestUri, '/');

        /** /docs/{path*} -> /docs/(?P<path>.*) */
        $pattern = preg_replace('/\{(\w+)\*}/', '(?P<$1>.*)', $uri);
        if (! is_string($pattern)) {
            return null;
        }

        /** /posts/{id} -> /posts/(?P<id>[^/]+) */
        $pattern = preg_replace('/\{(\w+)}/', '(?P<$1>[^/]+)', $pattern);
        if (! is_string($pattern)) {
            return null;
        }

        $pattern = '#^'.$pattern.'$#';

        if (! preg_match($pattern, $requestUri, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (! is_int($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
