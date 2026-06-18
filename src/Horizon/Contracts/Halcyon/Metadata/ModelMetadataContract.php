<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Metadata;

interface ModelMetadataContract
{
    public function getClass(): string;

    public function getTable(): string;

    /**
     * @return array<string, PropertyMetadataContract>
     */
    public function getProperties(): array;

    /**
     * @return array<string, RelationMetadataContract>
     */
    public function getRelations(): array;

    /**
     * @return array<string, class-string>
     */
    public function getCasts(): array;

    /**
     * @return array<string>
     */
    public function getHidden(): array;

    /**
     * @return array<class-string>
     */
    public function getObservers(): array;

    /**
     * @return array<class-string>
     */
    public function getScopes(): array;
}
