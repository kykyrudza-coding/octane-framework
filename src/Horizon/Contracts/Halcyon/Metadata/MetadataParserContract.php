<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Metadata;

interface MetadataParserContract
{
    /**
     * @param class-string $class
     */
    public function parse(string $class): ModelMetadataContract;
}
