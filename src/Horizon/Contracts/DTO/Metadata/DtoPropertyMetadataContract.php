<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO\Metadata;

interface DtoPropertyMetadataContract
{
    public function getName(): string;

    public function getInputName(): string;

    public function getOutputName(): string;

    public function getType(): ?string;

    public function isNullable(): bool;

    public function isCollection(): bool;

    public function getCollectionValueType(): ?string;

    public function getCast(): ?string;

    public function hasDefaultValue(): bool;

    public function getDefaultValue(): mixed;

    public function isConstructorParameter(): bool;
}
