<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Schema;

interface SchemaBuilderContract
{
    public function create(string $table, array $columns): void;

    public function alter(string $table, array $columns): void;

    public function drop(string $table): void;

    public function dropIfExists(string $table): void;

    public function rename(string $from, string $to): void;

    public function hasTable(string $table): bool;

    public function hasColumn(string $table, string $column): bool;
}
