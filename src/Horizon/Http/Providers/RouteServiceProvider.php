<?php

declare(strict_types=1);

namespace Horizon\Http\Providers;

use Horizon\Support\Providers\ServiceProvider;
use Horizon\Contracts\Http\Middleware\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Response\ResponseFactoryContract;
use Horizon\Http\Collection\MiddlewareCollection;
use Horizon\Http\Middleware\ConvertEmptyStringsToNull;
use Horizon\Http\Middleware\ValidatePostSize;
use Horizon\Http\Response\ResponseFactory;
use RuntimeException;

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
            throw new RuntimeException('Middleware collection binding must resolve to a MiddlewareCollectionContract instance.');
        }

        $collection->web([
            ValidatePostSize::class,
        ]);

        $collection->global([
            ConvertEmptyStringsToNull::class,
        ]);
    }
}
