<?php

declare(strict_types=1);

namespace Horizon\Database\Connections;

use Horizon\Contracts\Database\Connections\ConnectionContract;
use Horizon\Contracts\Database\Connections\ConnectionFactoryContract;
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Database\Exceptions\DatabaseConfigurationException;

final class ConnectionManager implements ConnectionManagerContract
{
    /**
     * @var array<string, ConnectionContract>
     */
    private array $connections = [];

    public function __construct(
        private readonly ConnectionFactoryContract $factory,
        private readonly array $config,
    ) {}

    public function connection(string $name = 'default'): ConnectionContract
    {
        $name = $name === 'default'
            ? ($this->config['default_connection'] ?? 'default')
            : $name;

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        return $this->connections[$name] = $this->makeConnection($name);
    }

    public function extend(string $driver, string $driverClass): void
    {
        $this->factory->extend($driver, $driverClass);
    }

    public function reconnect(string $name = 'default'): ConnectionContract
    {
        $this->disconnect($name);

        return $this->connection($name);
    }

    public function disconnect(string $name = 'default'): void
    {
        $name = $name === 'default'
            ? ($this->config['default_connection'] ?? 'default')
            : $name;

        unset($this->connections[$name]);
    }

    private function makeConnection(string $name): ConnectionContract
    {
        $config = $this->config['connections'][$name] ?? null;

        if ($config === null) {
            throw new DatabaseConfigurationException(
                "Connection [$name] not configured."
            );
        }

        $connection = $this->factory->make($name, $config);

        if ($this->config['query_log']['enabled'] ?? false) {
            $connection->enableQueryLog();
        }

        return $connection;
    }
}
