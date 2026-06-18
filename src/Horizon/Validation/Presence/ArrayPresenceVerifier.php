<?php

declare(strict_types=1);

namespace Horizon\Validation\Presence;

use Horizon\Contracts\Validation\PresenceVerifierContract;

final readonly class ArrayPresenceVerifier implements PresenceVerifierContract
{
    /**
     * @param  array<string, list<array<string, mixed>>>  $tables
     */
    public function __construct(
        private array $tables,
    ) {}

    public function exists(string $table, string $column, mixed $value): bool
    {
        foreach ($this->tables[$table] ?? [] as $row) {
            if (($row[$column] ?? null) === $value) {
                return true;
            }
        }

        return false;
    }
}
