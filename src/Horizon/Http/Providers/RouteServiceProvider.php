<?php

declare(strict_types=1);

namespace Horizon\Http\Providers;

use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Http\Console\StartServerCommand;
use Horizon\Support\Providers\ServiceProvider;
use Horizon\Contracts\Http\Collection\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Response\ResponseFactoryContract;
use Horizon\Http\Collection\MiddlewareCollection;
use Horizon\Http\Middleware\ConvertEmptyStringsToNull;
use Horizon\Http\Middleware\ValidatePostSize;
use Horizon\Http\Response\ResponseFactory;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            MiddlewareCollectionContract::class,
            MiddlewareCollection::class
        );

        $this->app->singleton(
            ResponseFactoryContract::class,
            ResponseFactory::class
        );
    }

    public function boot(): void
    {
        $collection = $this->app->make(MiddlewareCollectionContract::class);
        if (! $collection instanceof MiddlewareCollectionContract) {
            throw new BindingResolutionException('Middleware collection binding must resolve to a MiddlewareCollectionContract instance.');
        }

        $collection->web([
            ValidatePostSize::class,
        ]);

        $collection->global([
            ConvertEmptyStringsToNull::class,
        ]);

        $commands = $this->app->make(CommandRegistryContract::class);

        $commands->register(
            StartServerCommand::class
        );
    }
}
