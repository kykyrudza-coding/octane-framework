<?php

declare(strict_types=1);

namespace Horizon\Support\Facades;

use Horizon\Contracts\Halcyon\OrmConfiguratorContract;

/**
 * @method static void observe(class-string $model, class-string $observer)
 * @method static void scope(class-string $model, class-string $scope)
 * @method static void morphMap(array<string, class-string> $map)
 * @method static array<class-string, list<class-string>> getObservers()
 * @method static array<class-string, list<class-string>> getScopes()
 * @method static array<string, class-string> getMorphMap()
 *
 * @see \Horizon\Halcyon\Halcyon
 */
final class Halcyon extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OrmConfiguratorContract::class;
    }
}
