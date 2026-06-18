<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Metadata;

use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;
use Horizon\Contracts\Halcyon\Metadata\PropertyMetadataContract;
use Horizon\Contracts\Halcyon\Metadata\RelationMetadataContract;

final readonly class ModelMetadata implements ModelMetadataContract
{
    public function __construct(
        private string $class,
        private string $table,
        /** @var array<string, PropertyMetadataContract> */
        private array  $properties,
        /** @var array<string, RelationMetadataContract> */
        private array  $relations,
        /** @var array<string, class-string> */
        private array  $casts,
        /** @var array<string> */
        private array  $hidden,
        /** @var array<class-string> */
        private array  $observers,
        /** @var array<class-string> */
        private array  $scopes,
    ) {}

    public function getClass(): string
    {
        return $this->class;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array<string, PropertyMetadataContract>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array<string, RelationMetadataContract>
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return array<string, class-string>
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * @return array<string>
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * @return array<class-string>
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * @return array<class-string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
