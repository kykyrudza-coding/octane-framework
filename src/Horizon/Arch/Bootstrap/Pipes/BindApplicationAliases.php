<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Contracts\Arch\Application\ApplicationContract;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Support\Pipeline\PipeInterface;

class BindApplicationAliases implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $app = $payload->app;

        $app->bindAlias('app', ApplicationContract::class);
        $app->bindAlias('container', ContainerContract::class);
        $app->bindAlias('config', ConfigRepositoryContract::class);

        return $next($payload);
    }
}
