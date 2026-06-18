<?php

declare(strict_types=1);

namespace Horizon\Dto\Metadata;

use Horizon\Contracts\DTO\Metadata\DtoPropertyMetadataContract;

final readonly class DtoPropertyMetadata implements DtoPropertyMetadataContract
{
    public function __construct(
        private string $name,
        private string $inputName,
        private string $outputName,
        private ?string $type,
        private bool $nullable,
        private bool $collection,
        private ?string $collectionValueType,
        private ?string $cast,
        private bool $hasDefaultValue,
        private mixed $defaultValue,
        private bool $constructorParameter,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getInputName(): string
    {
        return $this->inputName;
    }

    public function getOutputName(): string
    {
        return $this->outputName;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }

    public function getCollectionValueType(): ?string
    {
        return $this->collectionValueType;
    }

    public function getCast(): ?string
    {
        return $this->cast;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function isConstructorParameter(): bool
    {
        return $this->constructorParameter;
    }
}
