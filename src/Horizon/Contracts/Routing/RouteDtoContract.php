<?php

declare(strict_types=1);

namespace Horizon\Contracts\Routing;

use Closure;

interface RouteDtoContract
{
    /**
     * @return list<string>
     */
    public function methods(): array;

    public function uri(): string;

    /**
     * @return Closure|array{0: class-string, 1: string}|string
     */
    public function action(): Closure|array|string;

    /**
     * @return list<string>
     */
    public function middleware(): array;

    public function name(): ?string;

    public function prefix(): ?string;

    public function routeGroup(): ?string;
}
