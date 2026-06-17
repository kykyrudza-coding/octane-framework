<?php

declare(strict_types=1);

namespace Horizon\Database\Connections;

use Horizon\Contracts\Arch\Application\ApplicationContract;
use Horizon\Contracts\Database\Connections\ConnectionContract;
use Horizon\Contracts\Database\Connections\ConnectionFactoryContract;
use Horizon\Contracts\Database\Connections\Drivers\DriverContract;
use Horizon\Database\Connections\Drivers\MySqlDriver;
use Horizon\Database\Connections\Drivers\PostgresDriver;
use Horizon\Database\Connections\Drivers\SqliteDriver;
use InvalidArgumentException;

final class ConnectionFactory implements ConnectionFactoryContract
{

    /**
     * @var array<string, DriverContract>
     */
    private array $drivers = [];

    public function __construct(
        private readonly ApplicationContract $app,
    ) {
        $this->registerDefaultDrivers();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function make(string $name, array $config): ConnectionContract
    {
        $driverName = $config['driver'] ?? null;

        if (! is_string($driverName) || $driverName === '') {
            throw new InvalidArgumentException(
                'Database connection config must specify a [driver].'
            );
        }

        $driver = $this->resolveDriver($driverName);

        $pdo = $driver->connect($config);

        return new Connection($pdo, $name, $driver->getName());
    }

    public function extend(string $driverName, DriverContract $driver): void
    {
        $this->drivers[$driverName] = $driver;
    }

    private function resolveDriver(string $driverName): DriverContract
    {
        $driver = $this->drivers[$driverName] ?? null;

        if ($driver === null) {
            throw new InvalidArgumentException(
                "Driver [$driverName] not supported."
            );
        }

        return $driver;
    }

    private function registerDefaultDrivers(): void
    {
        $this->drivers = [
            'mysql' => new MysqlDriver(),
            'pgsql' => new PostgresDriver(),
            'sqlite' => new SqliteDriver($this->app),
        ];
    }
}
