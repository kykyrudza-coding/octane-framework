<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Migrations;

interface MigrationRepositoryContract
{
    /**
     * @return list<string>
     */
    public function getRan(): array;

    /**
     * @param list<string> $files
     * @return list<string>
     */
    public function getPending(array $files): array;

    public function store(string $filename, int $batch): void;

    public function delete(string $filename): void;

    public function getLastBatch(): int;


    public function getBatch(string $filename): int;

    public function tableExists(): bool;

    public function createTable(): void;
}
