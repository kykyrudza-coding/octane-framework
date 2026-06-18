<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO\Metadata\Cache;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataContract;

interface DtoMetadataCacheContract
{
    /**
     * @param  class-string<DtoContract>  $class
     */
    public function get(string $class): ?DtoMetadataContract;

    /**
     * @param  class-string<DtoContract>  $class
     */
    public function put(string $class, DtoMetadataContract $metadata): void;

    /**
     * @param  class-string<DtoContract>  $class
     */
    public function forget(string $class): void;

    public function flush(): void;
}
