<?php

declare(strict_types=1);

namespace Horizon\Routing;

use Horizon\Contracts\Routing\RouteDtoContract;
use Horizon\Contracts\Routing\RouteMatchContract;

final readonly class RouteMatch implements RouteMatchContract
{
    /**
     * @param  array<string, string>  $params
     */
    public function __construct(
        private RouteDtoContract $route,
        private array $params = [],
    ) {}

    public function getRoute(): RouteDtoContract
    {
        return $this->route;
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
