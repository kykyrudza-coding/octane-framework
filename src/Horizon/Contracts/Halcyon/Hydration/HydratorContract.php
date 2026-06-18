<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Hydration;

use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;
use Horizon\Contracts\Halcyon\Model\ModelContract;
use Horizon\Support\ItemsList;

interface HydratorContract
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function hydrate(ModelMetadataContract $metadata, array $rows): ItemsList;

    /**
     * @param array<string, mixed> $row
     */
    public function hydrateOne(ModelMetadataContract $metadata, array $row): ModelContract;
}
