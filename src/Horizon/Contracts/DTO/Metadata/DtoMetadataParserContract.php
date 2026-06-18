<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO\Metadata;

use Horizon\Contracts\DTO\DtoContract;

interface DtoMetadataParserContract
{
    /**
     * @param  class-string<DtoContract>  $class
     */
    public function parse(string $class): DtoMetadataContract;
}
