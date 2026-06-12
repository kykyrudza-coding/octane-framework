<?php

declare(strict_types=1);

namespace Horizon\Contracts\Routing;

interface RouteMatchContract
{
    public function getRoute(): RouteDtoContract;

    /**
     * @return array<string, string>
     */
    public function getParams(): array;
}
