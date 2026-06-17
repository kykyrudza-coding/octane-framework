<?php

declare(strict_types=1);

namespace Horizon\Database\Connections\Drivers;

use Horizon\Contracts\Database\Connections\Drivers\DriverContract;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresDriver implements DriverContract
{
    public function connect(array $config): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 5432,
            $config['database'],
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $this->buildOptions($config),
            );

            if ($config['charset'] ?? null) {
                $pdo->exec("SET NAMES '{$config['charset']}'");
            }

            if ($config['schema'] ?? null) {
                $pdo->exec("SET search_path TO {$config['schema']}");
            }

            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException(
                "PostgreSQL connection failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    public function getName(): string
    {
        return 'pgsql';
    }

    private function buildOptions(array $config): array
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($config['options']['timeout'] ?? null) {
            $options[PDO::ATTR_TIMEOUT] = $config['options']['timeout'];
        }

        return $options;
    }
}
