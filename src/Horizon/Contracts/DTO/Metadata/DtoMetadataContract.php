<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO\Metadata;

use Horizon\Contracts\DTO\DtoContract;

interface DtoMetadataContract
{
    /**
     * @return class-string<DtoContract>
     */
    public function getClass(): string;

    /**
     * @return array<string, DtoPropertyMetadataContract>
     */
    public function getProperties(): array;

    public function getProperty(string $name): ?DtoPropertyMetadataContract;

    /**
     * @return list<DtoPropertyMetadataContract>
     */
    public function getConstructorParameters(): array;
}
