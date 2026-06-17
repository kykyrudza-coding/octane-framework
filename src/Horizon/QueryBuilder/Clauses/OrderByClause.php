<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Clauses;

final readonly class OrderByClause
{
    public function __construct(
        public string $column,
        public string $direction = 'ASC',
    ) {}
}
