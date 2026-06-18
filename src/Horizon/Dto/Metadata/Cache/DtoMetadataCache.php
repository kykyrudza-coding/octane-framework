<?php

declare(strict_types=1);

namespace Horizon\Dto\Metadata\Cache;

use Horizon\Contracts\DTO\Metadata\Cache\DtoMetadataCacheContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataContract;

class DtoMetadataCache implements DtoMetadataCacheContract
{
    /**
     * @var array<class-string, DtoMetadataContract>
     */
    private array $items = [];

    public function get(string $class): ?DtoMetadataContract
    {
        return $this->items[$class] ?? null;
    }

    public function put(string $class, DtoMetadataContract $metadata): void
    {
        $this->items[$class] = $metadata;
    }

    public function forget(string $class): void
    {
        unset($this->items[$class]);
    }

    public function flush(): void
    {
        $this->items = [];
    }
}
