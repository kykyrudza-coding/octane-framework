<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Providers;

use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Halcyon\Hydration\HydratorContract;
use Horizon\Contracts\Halcyon\Metadata\Cache\MetadataCacheContract;
use Horizon\Contracts\Halcyon\Metadata\MetadataParserContract;
use Horizon\Contracts\Halcyon\Metadata\MetadataRepositoryContract;
use Horizon\Contracts\Halcyon\OrmConfiguratorContract;
use Horizon\Contracts\QueryBuilder\QueryResultMapperContract;
use Horizon\Halcyon\Halcyon;
use Horizon\Halcyon\Hydration\Hydrator;
use Horizon\Halcyon\Metadata\Cache\FileMetadataCache;
use Horizon\Halcyon\Metadata\MetadataParser;
use Horizon\Halcyon\Metadata\MetadataRepository;
use Horizon\Halcyon\Query\HalcyonResultMapper;
use Horizon\Support\Providers\ServiceProvider;

final class HalcyonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerHalcyon();
        $this->registerMetadataCache();
        $this->registerMetadataParser();
        $this->registerMetadataRepository();
        $this->registerHydrator();
        $this->registerQueryResultMapper();
    }

    public function boot(): void
    {
        $this->configureOrm();
    }

    private function registerHalcyon(): void
    {
        $this->app->singleton(
            OrmConfiguratorContract::class,
            Halcyon::class,
        );
    }

    private function registerMetadataCache(): void
    {
        $this->app->singleton(
            MetadataCacheContract::class,
            function () {
                $config = $this->app->has(ConfigRepositoryContract::class)
                    ? $this->app->make(ConfigRepositoryContract::class)
                    : null;
                $path = $config instanceof ConfigRepositoryContract
                    ? $config->get('halcyon.metadata.cache.path')
                    : null;

                return new FileMetadataCache(
                    cachePath: is_string($path) && $path !== ''
                        ? $path
                        : $this->app->make('path.cache').DIRECTORY_SEPARATOR.'halcyon',
                );
            },
        );
    }

    private function registerMetadataParser(): void
    {
        $this->app->singleton(
            MetadataParserContract::class,
            fn () => new MetadataParser,
        );
    }

    private function registerMetadataRepository(): void
    {
        $this->app->singleton(
            MetadataRepositoryContract::class,
            function () {
                $config = $this->app->make(ConfigRepositoryContract::class);

                return new MetadataRepository(
                    parser: $this->app->make(MetadataParserContract::class),
                    cache: $this->app->make(MetadataCacheContract::class),
                    cacheEnabled: $config instanceof ConfigRepositoryContract
                        ? (bool) $config->get('halcyon.metadata.cache.enabled', false)
                        : false,
                );
            },
        );
    }

    private function registerHydrator(): void
    {
        $this->app->singleton(
            HydratorContract::class,
            fn () => new Hydrator,
        );
    }

    private function registerQueryResultMapper(): void
    {
        $this->app->singleton(
            QueryResultMapperContract::class,
            fn () => new HalcyonResultMapper(
                metadata: $this->app->make(MetadataRepositoryContract::class),
                hydrator: $this->app->make(HydratorContract::class),
            ),
        );
    }

    private function configureOrm(): void
    {
        if (! $this->app->has(ConfigRepositoryContract::class)) {
            return;
        }

        $config = $this->app->make(ConfigRepositoryContract::class);
        $halcyon = $this->app->make(OrmConfiguratorContract::class);

        if (! $config instanceof ConfigRepositoryContract || ! $halcyon instanceof OrmConfiguratorContract) {
            return;
        }

        foreach ($this->classMap($config->get('halcyon.orm.observers', [])) as $model => $classes) {
            foreach ($classes as $observer) {
                $halcyon->observe($model, $observer);
            }
        }

        foreach ($this->classMap($config->get('halcyon.orm.scopes', [])) as $model => $classes) {
            foreach ($classes as $scope) {
                $halcyon->scope($model, $scope);
            }
        }

        $morphMap = $config->get('halcyon.orm.morph_map', []);
        if (! is_array($morphMap) || $morphMap === []) {
            $morphMap = $config->get('halcyon.relations.morph_map', []);
        }

        if (is_array($morphMap)) {
            $halcyon->morphMap($this->stringMap($morphMap));
        }
    }

    /**
     * @return array<class-string, list<class-string>>
     */
    private function classMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $map = [];

        foreach ($value as $model => $classes) {
            if (! is_string($model)) {
                continue;
            }

            $classes = is_string($classes) ? [$classes] : $classes;

            if (! is_array($classes)) {
                continue;
            }

            $map[$model] = array_values(array_filter(
                $classes,
                static fn (mixed $class): bool => is_string($class) && $class !== '',
            ));
        }

        return $map;
    }

    /**
     * @return array<string, class-string>
     */
    private function stringMap(array $value): array
    {
        $map = [];

        foreach ($value as $alias => $class) {
            if (is_string($alias) && is_string($class) && $class !== '') {
                $map[$alias] = $class;
            }
        }

        return $map;
    }
}
