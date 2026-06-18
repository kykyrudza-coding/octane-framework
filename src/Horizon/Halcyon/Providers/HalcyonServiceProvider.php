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

    public function boot(): void {}

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
            fn () => new FileMetadataCache(
                cachePath: $this->app->make('path.cache').DIRECTORY_SEPARATOR.'halcyon',
            ),
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
}
