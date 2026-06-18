<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Relations;

use Horizon\Contracts\Halcyon\Relations\RelationContract;

final readonly class BelongsToMany implements RelationContract
{
    public function __construct(
        private string $name,
        private string $related,
        private string $pivotTable,
        private string $foreignKey,
        private string $localKey,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function getPivotTable(): string
    {
        return $this->pivotTable;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }
}
