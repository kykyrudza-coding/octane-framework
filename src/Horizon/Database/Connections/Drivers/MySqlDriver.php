<?php

declare(strict_types=1);

namespace Horizon\Database\Connections\Drivers;

use Horizon\Contracts\Database\Connections\Drivers\DriverContract;
use Horizon\Database\Exceptions\DatabaseConnectionException;
use PDO;
use Pdo\Mysql;
use PDOException;

final class MySqlDriver implements DriverContract
{

    public function connect(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 3306,
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            return new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $this->buildOptions($config)
            );
        } catch (PDOException $e) {
            throw new DatabaseConnectionException(
                "MySQL connection failed: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    public function getName(): string
    {
        return 'mysql';
    }

    private function buildOptions(array $config): array
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($config['options']['strict'] ?? true) {
            if (defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
                $options[Mysql::ATTR_INIT_COMMAND] = "SET SESSION sql_mode='STRICT_ALL_TABLES'";
            } else {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET SESSION sql_mode='STRICT_ALL_TABLES'";
            }
        }

        if ($config['options']['timeout'] ?? null) {
            $options[PDO::ATTR_TIMEOUT] = $config['options']['timeout'];
        }

        return $options;
    }
}
