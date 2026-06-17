<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Clauses;

final readonly class LimitClause
{
    public function __construct(
        public int $limit,
        public int $offset = 0,
    ) {}
}
