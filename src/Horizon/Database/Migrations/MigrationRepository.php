<?php

declare(strict_types=1);

namespace Horizon\Database\Migrations;

use Horizon\Contracts\Database\Connections\ConnectionContract;
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Contracts\Database\Migrations\MigrationRepositoryContract;
use RuntimeException;
use Throwable;

final class MigrationRepository implements MigrationRepositoryContract
{
    private const string TABLE = 'octane_migrations';

    public function __construct(
        private readonly ConnectionManagerContract $manager,
    ) {}

    private function connection(): ConnectionContract
    {
        return $this->manager->connection();
    }

    /**
     * @return list<string>
     */
    public function getRan(): array
    {
        $rows = $this->connection()->select(
            'SELECT filename FROM '.self::TABLE.' ORDER BY batch ASC, filename ASC',
        );

        $files = [];

        foreach ($rows as $row) {
            if (! is_array($row) || ! is_string($row['filename'] ?? null)) {
                throw new RuntimeException('Migration repository returned an invalid filename row.');
            }

            $files[] = $row['filename'];
        }

        return $files;
    }

    /**
     * @param list<string> $files
     * @return list<string>
     */
    public function getPending(array $files): array
    {
        $ran = $this->getRan();

        return array_values(
            array_filter(
                $files,
                fn (string $file): bool => ! in_array($file, $ran, true),
            ),
        );
    }

    public function store(string $filename, int $batch): void
    {
        $this->connection()->insert(
            'INSERT INTO '.self::TABLE.' (filename, batch, executed_at) VALUES (?, ?, ?)',
            [$filename, $batch, date('Y-m-d H:i:s')],
        );
    }

    public function delete(string $filename): void
    {
        $this->connection()->delete(
            'DELETE FROM '.self::TABLE.' WHERE filename = ?',
            [$filename],
        );
    }

    public function getLastBatch(): int
    {
        $result = $this->connection()->select(
            'SELECT MAX(batch) as batch FROM '.self::TABLE,
        );

        $row = $result[0] ?? null;

        if (! is_array($row)) {
            return 0;
        }

        $batch = $row['batch'] ?? 0;

        if (is_int($batch)) {
            return $batch;
        }

        if (is_string($batch) && ctype_digit($batch)) {
            return (int) $batch;
        }

        return 0;
    }

    public function getBatch(string $filename): int
    {
        $result = $this->connection()->select(
            'SELECT batch FROM '.self::TABLE.' WHERE filename = ? LIMIT 1',
            [$filename],
        );

        $row = $result[0] ?? null;

        if (! is_array($row)) {
            return 0;
        }

        $batch = $row['batch'] ?? 0;

        if (is_int($batch)) {
            return $batch;
        }

        if (is_string($batch) && ctype_digit($batch)) {
            return (int) $batch;
        }

        return 0;
    }

    public function tableExists(): bool
    {
        try {
            $this->connection()->select('SELECT 1 FROM '.self::TABLE.' LIMIT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function createTable(): void
    {
        $this->connection()->raw(
            'CREATE TABLE IF NOT EXISTS '.self::TABLE.' (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                filename    VARCHAR(255) NOT NULL,
                batch       INTEGER NOT NULL,
                executed_at TIMESTAMP NOT NULL
            )',
        );
    }
}
