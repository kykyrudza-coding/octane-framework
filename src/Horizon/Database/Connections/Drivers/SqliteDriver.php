<?php

declare(strict_types=1);

namespace Horizon\Database\Connections\Drivers;

use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Database\Connections\Drivers\DriverContract;
use Horizon\Database\Exceptions\DatabaseConfigurationException;
use Horizon\Database\Exceptions\DatabaseConnectionException;
use PDO;
use PDOException;

final class SqliteDriver implements DriverContract
{
    public function __construct(
        private readonly ApplicationContract $app,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public function connect(array $config): PDO
    {
        $database = $config['database'] ?? ':memory:';

        if (! is_string($database) || $database === '') {
            throw new DatabaseConfigurationException(
                'SQLite connection config [database] must be a non-empty string.',
            );
        }

        if ($database !== ':memory:') {
            $database = $this->app->dbPath($database);

            if (! file_exists($database)) {
                $dir = dirname($database);

                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                touch($database);
            }
        }

        try {
            $pdo = new PDO(
                "sqlite:{$database}",
                options: $this->buildOptions(),
            );

            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA journal_mode = WAL');

            return $pdo;
        } catch (PDOException $e) {
            throw new DatabaseConnectionException(
                "SQLite connection failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    public function getName(): string
    {
        return 'sqlite';
    }

    /**
     * @return array<int, mixed>
     */
    private function buildOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
}
