<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Connections;

use Closure;

interface ConnectionContract
{
    public function select(string $query, array $bindings = []): array;

    public function insert(string $query, array $bindings = []): bool;

    public function update(string $query, array $bindings = []): int;

    public function delete(string $query, array $bindings = []): int;

    public function raw(string $query, array $bindings = []): mixed;

    public function transaction(Closure $callback): mixed;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollBack(): void;

    public function savepoint(string $name): void;

    public function rollbackTo(string $name): void;

    public function getName(): string;

    public function getDriverName(): string;
}
