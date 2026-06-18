<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Metadata;

interface PropertyMetadataContract
{
    public function getPhpName(): string;

    public function getColumnName(): string;

    public function getPhpType(): string;

    public function isNullable(): bool;

    public function getDefault(): mixed;
}
