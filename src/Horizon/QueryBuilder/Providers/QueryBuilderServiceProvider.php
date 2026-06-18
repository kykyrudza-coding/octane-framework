<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Providers;

use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Contracts\QueryBuilder\QueryResultMapperContract;
use Horizon\QueryBuilder\Exceptions\QueryBuilderConnectionException;
use Horizon\QueryBuilder\QueryBuilderFactory;
use Horizon\QueryBuilder\Results\RawQueryResultMapper;
use Horizon\Support\Providers\ServiceProvider;
use PDO;

final class QueryBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueryBuilderFactory::class, function () {
            $manager = $this->app->make(ConnectionManagerContract::class);

            if (! $manager instanceof ConnectionManagerContract) {
                throw new QueryBuilderConnectionException('Connection manager binding must resolve to a ConnectionManagerContract instance.');
            }

            $connectionName = $this->app->make('config')
                ->get('query-builder.default_connection', 'default');
            $connectionName = is_string($connectionName) && $connectionName !== ''
                ? $connectionName
                : 'default';

            $connection = $manager->connection($connectionName);

            if ($this->app->make('config')->get('query-builder.debug.log_queries', false) === true
                && method_exists($connection, 'enableQueryLog')) {
                $connection->enableQueryLog();
            }

            if (! method_exists($connection, 'getPdo')) {
                throw new QueryBuilderConnectionException('Database connection must expose a PDO instance for QueryBuilder.');
            }

            /** @var PDO $pdo */
            $pdo = $connection->getPdo();

            $mapper = $this->app->has(QueryResultMapperContract::class)
                ? $this->app->make(QueryResultMapperContract::class)
                : new RawQueryResultMapper;

            if (! $mapper instanceof QueryResultMapperContract) {
                $mapper = new RawQueryResultMapper;
            }

            return new QueryBuilderFactory($pdo, $connection->getDriverName(), $mapper);
        });
    }

    public function boot(): void {}
}
