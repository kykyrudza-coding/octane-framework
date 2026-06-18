<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Metadata;

interface RelationMetadataContract
{
    public function getName(): string;

    public function getMethod(): string;

    public function getType(): string;

    /**
     * @return class-string
     */
    public function getRelated(): string;

    public function getForeignKey(): string;

    public function getLocalKey(): string;

    public function getPivotTable(): ?string;
}
