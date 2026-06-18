<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Results;

use Horizon\Contracts\QueryBuilder\QueryResultMapperContract;
use Horizon\Support\ItemsList;

final class RawQueryResultMapper implements QueryResultMapperContract
{
    public function tableFor(string $model): ?string
    {
        return null;
    }

    public function map(?string $model, ItemsList $rows): ItemsList
    {
        return $rows;
    }
}
