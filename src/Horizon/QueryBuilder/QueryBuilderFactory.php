<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder;

use Horizon\QueryBuilder\Grammar\MySqlQueryGrammar;
use Horizon\QueryBuilder\Grammar\QueryGrammar;
use PDO;

final class QueryBuilderFactory
{
    /** @var array<string, class-string<QueryGrammar>> */
    private array $grammars = [
        'mysql'  => MySqlQueryGrammar::class,
        'pgsql'  => \Horizon\QueryBuilder\Grammar\PostgresQueryGrammar::class,
        'sqlite' => \Horizon\QueryBuilder\Grammar\SqliteQueryGrammar::class,
    ];

    public function __construct(
        private readonly PDO $connection,
        private readonly string $driver = 'mysql',
    ) {}

    public function make(): QueryBuilder
    {
        $grammarClass = $this->grammars[$this->driver]
            ?? throw new \InvalidArgumentException("No grammar registered for driver [{$this->driver}].");

        return new QueryBuilder($this->connection, new $grammarClass());
    }

    public function forTable(string $table): QueryBuilder
    {
        return $this->make()->table($table);
    }

    /** @param class-string $model */
    public function forModel(string $model): QueryBuilder
    {
        return $this->make()->for($model);
    }
}
