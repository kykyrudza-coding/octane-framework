<?php

declare(strict_types=1);

namespace Horizon\Dto\Providers;

use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
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
            function () {
                $cacheEnabled = $this->configBool('dto.metadata.cache.enabled', false);

                return new DtoMetadataRepository(
                    parser: $this->app->make(DtoMetadataParserContract::class),
                    cache: $cacheEnabled
                        ? $this->app->make(DtoMetadataCacheContract::class)
                        : null,
                );
            },
        );

        $this->app->singleton(
            DtoMapperContract::class,
            fn () => new DtoMapper(
                metadata: $this->app->make(DtoMetadataRepositoryContract::class),
                strict: $this->configBool('dto.mapping.strict', true),
                unknownFields: $this->configString('dto.mapping.unknown_fields', 'ignore'),
                missingFields: $this->configString('dto.mapping.missing_fields', 'throw'),
            ),
        );

        $this->app->singleton(
            DtoSerializerContract::class,
            fn () => new DtoSerializer(
                metadata: $this->app->make(DtoMetadataRepositoryContract::class),
                includeNull: $this->configBool('dto.serialization.include_null', true),
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

    private function configBool(string $key, bool $default): bool
    {
        $value = $this->config($key, $default);

        return is_bool($value) ? $value : $default;
    }

    private function configString(string $key, string $default): string
    {
        $value = $this->config($key, $default);

        return is_string($value) && $value !== '' ? $value : $default;
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
}
