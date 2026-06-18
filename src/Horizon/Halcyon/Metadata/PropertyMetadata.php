<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Metadata;

use Horizon\Contracts\Halcyon\Metadata\PropertyMetadataContract;

final readonly class PropertyMetadata implements PropertyMetadataContract
{
    public function __construct(
        private string $phpName,
        private string $columnName,
        private string $phpType,
        private bool $nullable,
        private mixed $default,
    ) {}

    public function getPhpName(): string
    {
        return $this->phpName;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getPhpType(): string
    {
        return $this->phpType;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }
}
