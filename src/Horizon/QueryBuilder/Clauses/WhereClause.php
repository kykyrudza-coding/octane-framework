<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Clauses;

use Horizon\QueryBuilder\Expressions\RawExpression;

final readonly class WhereClause
{
    public function __construct(
        public string|RawExpression $column,
        public string $operator,
        public mixed $value,
        public string $boolean = 'and',
        public bool $isRaw = false,
    ) {}

    public static function raw(string $sql, string $boolean = 'AND'): self
    {
        return new self(
            column: new RawExpression($sql),
            operator: '',
            value: null,
            boolean: $boolean,
            isRaw: true,
        );
    }
}
