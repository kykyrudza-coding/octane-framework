<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Providers;

use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\QueryBuilder\QueryBuilderFactory;
use Horizon\Support\Providers\ServiceProvider;
use PDO;
use RuntimeException;

final class QueryBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueryBuilderFactory::class, function () {
            $manager = $this->app->make(ConnectionManagerContract::class);

            if (! $manager instanceof ConnectionManagerContract) {
                throw new RuntimeException('Connection manager binding must resolve to a ConnectionManagerContract instance.');
            }

            $connection = $manager->connection();

            if (! method_exists($connection, 'getPdo')) {
                throw new RuntimeException('Database connection must expose a PDO instance for QueryBuilder.');
            }

            /** @var PDO $pdo */
            $pdo = $connection->getPdo();

            return new QueryBuilderFactory($pdo, $connection->getDriverName());
        });
    }

    public function boot(): void {}
}
