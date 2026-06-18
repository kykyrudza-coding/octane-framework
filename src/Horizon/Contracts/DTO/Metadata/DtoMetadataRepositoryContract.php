<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO\Metadata;

use Horizon\Contracts\DTO\DtoContract;

interface DtoMetadataRepositoryContract
{
    /**
     * @param  class-string<DtoContract>  $class
     */
    public function for(string $class): DtoMetadataContract;

    public function flush(): void;
}
