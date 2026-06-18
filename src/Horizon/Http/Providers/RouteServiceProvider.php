<?php

declare(strict_types=1);

namespace Horizon\Http\Providers;

use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Contracts\Http\Collection\MiddlewareCollectionContract;
use Horizon\Contracts\Http\Response\ResponseFactoryContract;
use Horizon\Http\Collection\MiddlewareCollection;
use Horizon\Http\Console\StartServerCommand;
use Horizon\Http\Middleware\ConvertEmptyStringsToNull;
use Horizon\Http\Middleware\TrimStrings;
use Horizon\Http\Middleware\ValidatePostSize;
use Horizon\Http\Response\ResponseFactory;
use Horizon\Support\Providers\ServiceProvider;

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
            fn () => new ResponseFactory(
                jsonFlags: (int) $this->config('http.responses.json_flags', JSON_THROW_ON_ERROR),
            ),
        );
    }

    public function boot(): void
    {
        $collection = $this->app->make(MiddlewareCollectionContract::class);
        if (! $collection instanceof MiddlewareCollectionContract) {
            throw new BindingResolutionException('Middleware collection binding must resolve to a MiddlewareCollectionContract instance.');
        }

        $this->registerConfiguredMiddleware($collection);

        $commands = $this->app->make(CommandRegistryContract::class);

        $commands->register(
            StartServerCommand::class
        );
    }

    private function registerConfiguredMiddleware(MiddlewareCollectionContract $collection): void
    {
        $requestOptions = $this->configArray('http.requests');
        $configured = $this->configArray('http.middleware');

        $global = [];
        $web = [];

        if (($requestOptions['trim_strings'] ?? false) === true) {
            $global[] = TrimStrings::class;
        }

        if (($requestOptions['convert_empty_strings_to_null'] ?? true) === true) {
            $global[] = ConvertEmptyStringsToNull::class;
        }

        if (($requestOptions['max_post_size_validation'] ?? true) === true) {
            $web[] = ValidatePostSize::class;
        }

        $collection->global($this->uniqueMiddleware([
            ...$global,
            ...$this->stringList($configured['global'] ?? []),
        ]));

        $collection->web($this->uniqueMiddleware([
            ...$web,
            ...$this->stringList($configured['web'] ?? []),
        ]));

        $collection->api($this->uniqueMiddleware(
            $this->stringList($configured['api'] ?? []),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function configArray(string $key): array
    {
        $config = $this->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return [];
        }

        $value = $config->get($key, []);

        return is_array($value) ? $value : [];
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if (! $this->app->has(ConfigRepositoryContract::class)) {
            return $default;
        }

        $config = $this->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return $default;
        }

        return $config->get($key, $default);
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (is_string($value) && $value !== '') {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            $value,
            static fn (mixed $item): bool => is_string($item) && $item !== '',
        ));
    }

    /**
     * @param  list<string>  $middleware
     * @return list<string>
     */
    private function uniqueMiddleware(array $middleware): array
    {
        return array_values(array_unique($middleware));
    }
}
