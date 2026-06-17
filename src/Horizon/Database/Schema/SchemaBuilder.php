<?php

declare(strict_types=1);

namespace Horizon\Database\Schema;

use Horizon\Contracts\Database\Connections\ConnectionContract;
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Contracts\Database\Schema\SchemaBuilderContract;
use Horizon\Contracts\Database\Schema\SchemaCompilerContract;
use Throwable;

final readonly class SchemaBuilder implements SchemaBuilderContract
{
    public function __construct(
        private ConnectionManagerContract $manager,
        private SchemaCompilerContract    $compiler,
    ) {}

    private function connection(): ConnectionContract
    {
        return $this->manager->connection();
    }

    public function create(string $table, array $columns): void
    {
        $this->connection()->raw(
            $this->compiler->compileCreate($table, $columns),
        );
    }

    public function alter(string $table, array $columns): void
    {
        $this->connection()->raw(
            $this->compiler->compileAlter($table, $columns),
        );
    }

    public function drop(string $table): void
    {
        $this->connection()->raw(
            $this->compiler->compileDrop($table),
        );
    }

    public function dropIfExists(string $table): void
    {
        $this->connection()->raw(
            $this->compiler->compileDropIfExists($table),
        );
    }

    public function rename(string $from, string $to): void
    {
        $this->connection()->raw(
            $this->compiler->compileRename($from, $to),
        );
    }

    public function hasTable(string $table): bool
    {
        try {
            $this->connection()->select("SELECT 1 FROM \"$table\" LIMIT 1");

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function hasColumn(string $table, string $column): bool
    {
        $result = $this->connection()->select(
            "PRAGMA table_info(\"$table\")",
        );

        return array_any($result, fn($col) => $col['name'] === $column);
    }
}
