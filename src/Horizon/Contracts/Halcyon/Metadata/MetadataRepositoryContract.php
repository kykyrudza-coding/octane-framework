<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Metadata;

interface MetadataRepositoryContract
{
    /**
     * @param class-string $class
     */
    public function for(string $class): ModelMetadataContract;

    public function flush(): void;
}
