<?php

declare(strict_types=1);

namespace Horizon\Dto\Providers;

use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\DTO\DtoMapperContract;
use Horizon\Contracts\DTO\DtoSerializerContract;
use Horizon\Contracts\DTO\Metadata\Cache\DtoMetadataCacheContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataParserContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataRepositoryContract;
use Horizon\Dto\DtoFactory;
use Horizon\Dto\DtoMapper;
use Horizon\Dto\DtoSerializer;
use Horizon\Dto\Metadata\Cache\DtoMetadataCache;
use Horizon\Dto\Metadata\DtoMetadataParser;
use Horizon\Dto\Metadata\DtoMetadataRepository;
use Horizon\Support\Providers\ServiceProvider;

final class DtoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            DtoMetadataCacheContract::class,
            DtoMetadataCache::class,
        );

        $this->app->singleton(
            DtoMetadataParserContract::class,
            DtoMetadataParser::class,
        );

        $this->app->singleton(
            DtoMetadataRepositoryContract::class,
            fn () => new DtoMetadataRepository(
                parser: $this->app->make(DtoMetadataParserContract::class),
                cache: $this->app->make(DtoMetadataCacheContract::class),
            ),
        );

        $this->app->singleton(
            DtoMapperContract::class,
            fn () => new DtoMapper(
                metadata: $this->app->make(DtoMetadataRepositoryContract::class),
            ),
        );

        $this->app->singleton(
            DtoSerializerContract::class,
            fn () => new DtoSerializer(
                metadata: $this->app->make(DtoMetadataRepositoryContract::class),
            ),
        );

        $this->app->singleton(
            DtoFactoryContract::class,
            fn () => new DtoFactory(
                metadata: $this->app->make(DtoMetadataRepositoryContract::class),
                mapper: $this->app->make(DtoMapperContract::class),
            ),
        );
    }
}
