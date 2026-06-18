<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Metadata;

use Horizon\Contracts\Halcyon\Metadata\RelationMetadataContract;

final readonly class RelationMetadata implements RelationMetadataContract
{
    public function __construct(
        private string $name,
        private string $method,
        private string $type,
        private string $related,
        private string $foreignKey,
        private string $localKey,
        private ?string $pivotTable = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getPivotTable(): ?string
    {
        return $this->pivotTable;
    }
}
