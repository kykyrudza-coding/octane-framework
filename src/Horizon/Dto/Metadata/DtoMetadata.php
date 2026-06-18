<?php

declare(strict_types=1);

namespace Horizon\Dto\Metadata;

use Horizon\Contracts\DTO\Metadata\DtoMetadataContract;
use Horizon\Contracts\DTO\Metadata\DtoPropertyMetadataContract;

final readonly class DtoMetadata implements DtoMetadataContract
{
    /**
     * @param  class-string  $class
     * @param  array<string, DtoPropertyMetadataContract>  $properties
     * @param  list<DtoPropertyMetadataContract>  $constructorParameters
     */
    public function __construct(
        private string $class,
        private array $properties,
        private array $constructorParameters,
    ) {}

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $name): ?DtoPropertyMetadataContract
    {
        return $this->properties[$name] ?? null;
    }

    public function getConstructorParameters(): array
    {
        return $this->constructorParameters;
    }
}
