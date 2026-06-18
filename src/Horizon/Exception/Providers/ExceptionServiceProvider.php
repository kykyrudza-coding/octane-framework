<?php

declare(strict_types=1);

namespace Horizon\Exception\Providers;

use Horizon\Support\Providers\ServiceProvider;
use Horizon\Contracts\Arch\ContainerContract;
use Horizon\Contracts\Exception\Renderers\ErrorRendererContract;
use Horizon\Contracts\Exception\HandlerContract;
use Horizon\Exception\Exceptions\RendererResolutionException;
use Horizon\Exception\Handler;
use Horizon\Exception\Renderers\ErrorRenderer;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ErrorRendererContract::class,
            ErrorRenderer::class
        );

        $this->app->singleton(
            HandlerContract::class,
            function (ContainerContract $app) {
                $renderer = $app->make(ErrorRendererContract::class);
                if (! $renderer instanceof ErrorRendererContract) {
                    throw new RendererResolutionException('Error renderer binding must resolve to an ErrorRendererContract instance.');
                }

                return new Handler(
                    $renderer
                );
            }
        );
    }
}
