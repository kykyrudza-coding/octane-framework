<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder;

use Horizon\QueryBuilder\Clauses\JoinClause;
use Horizon\QueryBuilder\Clauses\LimitClause;
use Horizon\QueryBuilder\Clauses\OrderByClause;
use Horizon\QueryBuilder\Clauses\WhereClause;
use Horizon\QueryBuilder\Exceptions\QueryBuilderException;
use Horizon\QueryBuilder\Grammar\QueryGrammar;
use Horizon\QueryBuilder\Results\QueryRow;
use Horizon\Support\ItemsList;
use PDO;
use ReflectionClass;
use ReflectionException;

/**
 * Fluent SQL query builder.
 *
 * Entry points:
 *   QueryBuilder::table('users')
 *   QueryBuilder::for(User::class)   — resolves table by convention until Halcyon exists
 */
final class QueryBuilder
{
    private ?string $table = null;

    /** @var list<string> */
    private array $columns = ['*'];

    /** @var list<WhereClause> */
    private array $wheres = [];

    /** @var list<OrderByClause> */
    private array $orders = [];

    /** @var list<JoinClause> */
    private array $joins = [];

    private ?LimitClause $limitClause = null;

    /** @var list<mixed> */
    private array $bindings = [];

    public function __construct(
        private readonly PDO $connection,
        private readonly QueryGrammar $grammar,
    ) {}

    // -------------------------------------------------------------------------
    // Table / model targeting
    // -------------------------------------------------------------------------

    public function table(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;

        return $clone;
    }

    /**
     * Target a model class. Resolves table name by convention (snake_case plural)
     * until Halcyon metadata is available.
     *
     * @param class-string $model
     * @throws ReflectionException
     */
    public function for(string $model): self
    {
        $short = new ReflectionClass($model)->getShortName();
        $table = $this->classNameToTable($short);

        return $this->table($table);
    }

    // -------------------------------------------------------------------------
    // SELECT
    // -------------------------------------------------------------------------

    public function select(string ...$columns): self
    {
        $clone = clone $this;
        $clone->columns = $columns !== [] ? array_values($columns) : ['*'];

        return $clone;
    }

    public function selectRaw(string $expression): self
    {
        $clone = clone $this;
        $clone->columns = [$expression];

        return $clone;
    }

    // -------------------------------------------------------------------------
    // WHERE
    // -------------------------------------------------------------------------

    public function where(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $clone = clone $this;
        $clone->wheres[] = new WhereClause($column, $operator, $value, $boolean);
        $clone->bindings[] = $value;

        return $clone;
    }

    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): self
    {
        $clone = clone $this;
        $clone->wheres[] = WhereClause::raw($sql, $boolean);

        foreach ($bindings as $binding) {
            $clone->bindings[] = $binding;
        }

        return $clone;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    // -------------------------------------------------------------------------
    // JOIN
    // -------------------------------------------------------------------------

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $clone = clone $this;
        $join = new JoinClause(strtoupper($type), $table);
        $join->on($first, $operator, $second);
        $clone->joins[] = $join;

        return $clone;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    // -------------------------------------------------------------------------
    // ORDER / LIMIT / OFFSET
    // -------------------------------------------------------------------------

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone = clone $this;
        $clone->orders[] = new OrderByClause($column, strtoupper($direction));

        return $clone;
    }

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $clone = clone $this;
        $clone->limitClause = new LimitClause($limit, $offset);

        return $clone;
    }

    public function offset(int $offset): self
    {
        $clone = clone $this;
        $clone->limitClause = new LimitClause($this->limitClause?->limit ?? PHP_INT_MAX, $offset);

        return $clone;
    }

    // -------------------------------------------------------------------------
    // READS
    // -------------------------------------------------------------------------

    /** @return ItemsList<int, QueryRow> */
    public function get(): ItemsList
    {
        $sql = $this->grammar->compileSelect(
            $this->resolveTable(),
            $this->columns,
            $this->joins,
            $this->wheres,
            $this->orders,
            $this->limitClause,
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ItemsList::from(
            array_map(
                static fn (array $row): QueryRow => new QueryRow($row),
                $rows,
            ),
        );
    }

    /** @return QueryRow|null */
    public function first(): mixed
    {
        $builder = $this->limitClause === null ? $this->limit(1) : $this;

        return $builder->get()->first();
    }

    public function count(string $column = '*'): int
    {
        $expr = $column === '*' ? 'COUNT(*)' : "COUNT({$column})";
        $sql = $this->grammar->compileSelect(
            $this->resolveTable(),
            [$expr],
            $this->joins,
            $this->wheres,
            [],
            null,
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);

        return (int) $stmt->fetchColumn();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    // -------------------------------------------------------------------------
    // WRITES
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $values */
    public function insert(array $values): bool
    {
        $sql = $this->grammar->compileInsert($this->resolveTable(), $values);
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(array_values($values));
    }

    /** @param array<string, mixed> $values */
    public function create(array $values): string|false
    {
        $this->insert($values);

        return $this->connection->lastInsertId();
    }

    /** @param array<string, mixed> $values */
    public function update(array $values): int
    {
        $sql = $this->grammar->compileUpdate($this->resolveTable(), $values, $this->wheres);

        $bindings = [...array_values($values), ...$this->bindings];

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = $this->grammar->compileDelete($this->resolveTable(), $this->wheres);
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    // -------------------------------------------------------------------------
    // Debug
    // -------------------------------------------------------------------------

    public function toSql(): string
    {
        return $this->grammar->compileSelect(
            $this->resolveTable(),
            $this->columns,
            $this->joins,
            $this->wheres,
            $this->orders,
            $this->limitClause,
        );
    }

    /** @return list<mixed> */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function resolveTable(): string
    {
        if ($this->table === null) {
            throw QueryBuilderException::missingTable();
        }

        return $this->table;
    }

    private function classNameToTable(string $className): string
    {
        // UserProfile -> user_profiles
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className) ?? $className);

        return $snake . 's';
    }
}
