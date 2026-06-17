<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Migrations;

interface MigrationRunnerContract
{
    public function run(string $path): void;

    public function rollback(string $path, int $batch): void;

    public function fresh(string $path): void;

    public function reset(string $path): void;
}
