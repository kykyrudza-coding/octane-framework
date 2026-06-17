<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Clauses;

final class JoinClause
{
    /** @var array<array{first: string, operator: string, second: string}> */
    private array $conditions = [];

    public function __construct(
        public readonly string $type,
        public readonly string $table,
    ) {}

    public function on(string $first, string $operator, string $second): self
    {
        $this->conditions[] = compact('first', 'operator', 'second');

        return $this;
    }

    /** @return array<array{first: string, operator: string, second: string}> */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
