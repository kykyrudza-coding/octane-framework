<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO;

use Horizon\Contracts\DTO\Metadata\DtoMetadataContract;

interface DtoMapperContract
{
    public function map(DtoMetadataContract $metadata, array|object $data): DtoContract;
}
