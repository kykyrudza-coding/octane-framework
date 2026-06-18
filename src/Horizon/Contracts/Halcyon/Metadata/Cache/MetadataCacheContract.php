<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Metadata\Cache;

use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;

interface MetadataCacheContract
{
    /**
     * @param class-string $class
     */
    public function has(string $class): bool;

    /**
     * @param class-string $class
     */
    public function get(string $class): ?ModelMetadataContract;

    /**
     * @param class-string $class
     */
    public function set(string $class, ModelMetadataContract $metadata): void;

    /**
     * @param class-string $class
     */
    public function forget(string $class): void;

    public function flush(): void;
}
