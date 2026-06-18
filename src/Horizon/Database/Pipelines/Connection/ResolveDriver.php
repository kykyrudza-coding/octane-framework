<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Connection;

use Closure;
use Horizon\Contracts\Database\Connections\Drivers\DriverContract;
use Horizon\Database\Connections\Drivers\MySqlDriver;
use Horizon\Database\Connections\Drivers\PostgresDriver;
use Horizon\Database\Connections\Drivers\SqliteDriver;
use Horizon\Database\Exceptions\DatabaseConfigurationException;
use Horizon\Support\Pipeline\PipeInterface;

final class ResolveDriver implements PipeInterface
{
    /**
     * @var array<string, class-string<DriverContract>>
     */
    private array $drivers = [
        'mysql'  => MySqlDriver::class,
        'pgsql'  => PostgresDriver::class,
        'sqlite' => SqliteDriver::class,
    ];

    public function handle(mixed $payload, Closure $next): array
    {
        $driver = $payload['config']['driver'] ?? null;

        if ($driver === null) {
            throw new DatabaseConfigurationException(
                'Database connection config must specify a [driver].',
            );
        }

        $driverClass = $this->drivers[$driver] ?? null;

        if ($driverClass === null) {
            throw new DatabaseConfigurationException(
                "Unsupported database driver [$driver].",
            );
        }

        $payload['driver'] = new $driverClass();

        return $next($payload);
    }
}
