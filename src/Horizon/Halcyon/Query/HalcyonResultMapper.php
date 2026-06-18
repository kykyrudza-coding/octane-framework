<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Query;

use Horizon\Contracts\Halcyon\Hydration\HydratorContract;
use Horizon\Contracts\Halcyon\Metadata\MetadataRepositoryContract;
use Horizon\Contracts\QueryBuilder\QueryResultMapperContract;
use Horizon\QueryBuilder\Results\QueryRow;
use Horizon\Support\ItemsList;

final readonly class HalcyonResultMapper implements QueryResultMapperContract
{
    public function __construct(
        private MetadataRepositoryContract $metadata,
        private HydratorContract $hydrator,
    ) {}

    public function tableFor(string $model): ?string
    {
        return $this->metadata->for($model)->getTable();
    }

    public function map(?string $model, ItemsList $rows): ItemsList
    {
        if ($model === null) {
            return $rows;
        }

        $payload = [];

        foreach ($rows as $row) {
            $payload[] = $row instanceof QueryRow ? $row->toArray() : (array) $row;
        }

        return $this->hydrator->hydrate(
            $this->metadata->for($model),
            $payload,
        );
    }
}
