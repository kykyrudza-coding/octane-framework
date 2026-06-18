<?php

declare(strict_types=1);

namespace Horizon\Support\Facades;

use Horizon\Arch\Application;
use Horizon\Contracts\Arch\ApplicationContract;


/**
 * @method static string version()
 * @method static void singleton(string $abstract, callable|string $concrete)
 * @method static void instance(string $abstract, object $instance)
 * @method static mixed make(string $abstract)
 *
 * @see Application
 */
class App extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApplicationContract::class;
    }
}
