<?php

declare(strict_types=1);

namespace Horizon\Contracts\QueryBuilder;

use Horizon\Support\ItemsList;

interface QueryResultMapperContract
{
    /**
     * @param class-string $model
     */
    public function tableFor(string $model): ?string;

    /**
     * @param class-string|null $model
     */
    public function map(?string $model, ItemsList $rows): ItemsList;
}
