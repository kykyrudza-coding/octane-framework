<?php

declare(strict_types=1);

namespace Horizon\Exception\Providers;

use Horizon\Support\Providers\ServiceProvider;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Exception\ErrorRendererContract;
use Horizon\Contracts\Exception\ExceptionHandlerContract;
use Horizon\Exception\Handler;
use Horizon\Exception\Renderers\ErrorRenderer;
use RuntimeException;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ErrorRendererContract::class,
            ErrorRenderer::class
        );

        $this->app->singleton(
            ExceptionHandlerContract::class,
            function (ContainerContract $app) {
                $renderer = $app->make(ErrorRendererContract::class);
                if (! $renderer instanceof ErrorRendererContract) {
                    throw new RuntimeException('Error renderer binding must resolve to an ErrorRendererContract instance.');
                }

                return new Handler(
                    $renderer
                );
            }
        );
    }
}
