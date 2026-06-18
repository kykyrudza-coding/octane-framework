<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Http\HttpKernel;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Arch\ContainerContract;
use Horizon\Contracts\Arch\Http\HttpKernelContract;
use Horizon\Support\Pipeline\PipeInterface;

class RegisterCoreBindings implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $app = $payload->app;

        $app->instance(
            ApplicationContract::class,
            $app
        );

        $app->instance(
            ContainerContract::class,
            $app->getContainer()
        );

        $app->singleton(
            HttpKernelContract::class,
            HttpKernel::class
        );

        return $next($payload);
    }
}
