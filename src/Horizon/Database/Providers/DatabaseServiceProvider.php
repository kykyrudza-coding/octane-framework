<?php

declare(strict_types=1);

namespace Horizon\Database\Providers;

use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Contracts\Database\Connections\ConnectionFactoryContract;
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Contracts\Database\Migrations\MigrationRepositoryContract;
use Horizon\Contracts\Database\Migrations\MigrationRunnerContract;
use Horizon\Contracts\Database\Schema\SchemaBuilderContract;
use Horizon\Database\Connections\ConnectionFactory;
use Horizon\Database\Connections\ConnectionManager;
use Horizon\Database\Console\MigrateCommand;
use Horizon\Database\Console\MigrateFreshCommand;
use Horizon\Database\Console\MigrateMakeCommand;
use Horizon\Database\Console\MigrateResetCommand;
use Horizon\Database\Console\MigrateRollbackCommand;
use Horizon\Database\Console\SeedCommand;
use Horizon\Database\Migrations\MigrationRepository;
use Horizon\Database\Migrations\MigrationRunner;
use Horizon\Database\Schema\Compilers\MySqlSchemaCompiler;
use Horizon\Database\Schema\Compilers\PostgresSchemaCompiler;
use Horizon\Database\Schema\Compilers\SqliteSchemaCompiler;
use Horizon\Database\Schema\SchemaBuilder;
use Horizon\Support\Providers\ServiceProvider;

final class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ConnectionFactoryContract::class,
            ConnectionFactory::class,
        );

        $this->app->singleton(
            ConnectionManagerContract::class,
            fn () => new ConnectionManager(
                $this->app->make(ConnectionFactoryContract::class),
                $this->app->make('config')->get('database'),
            ),
        );

        $this->app->singleton(
            MigrationRepositoryContract::class,
            fn () => new MigrationRepository(
                $this->app->make(ConnectionManagerContract::class),
            ),
        );

        $this->app->singleton(
            SchemaBuilderContract::class,
            fn () => new SchemaBuilder(
                $this->app->make(ConnectionManagerContract::class),
                $this->resolveCompiler(),
            ),
        );

        $this->app->singleton(
            MigrationRunnerContract::class,
            MigrationRunner::class,
        );
    }

    public function boot(): void
    {
        $registry = $this->app->make(CommandRegistryContract::class);

        $registry->register(MigrateCommand::class);
        $registry->register(MigrateRollbackCommand::class);
        $registry->register(MigrateFreshCommand::class);
        $registry->register(MigrateResetCommand::class);
        $registry->register(MigrateMakeCommand::class);
        $registry->register(SeedCommand::class);
    }

    private function resolveCompiler(): mixed
    {
        $driver = $this->app->make('config')
            ->get(
                'database.connections.'
                . $this->app->make('config')
                    ->get('database.default_connection')
                .'.driver'
            );

        return match ($driver) {
            'mysql'  => new MySqlSchemaCompiler(),
            'pgsql'  => new PostgresSchemaCompiler(),
            'sqlite' => new SqliteSchemaCompiler(),
            default  => new MySqlSchemaCompiler(),
        };
    }
}
