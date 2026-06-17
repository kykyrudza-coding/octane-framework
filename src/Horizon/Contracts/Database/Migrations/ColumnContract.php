<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Migrations;

interface ColumnContract
{
    public function nullable(): static;

    public function notNull(): static;

    public function default(mixed $value): static;

    public function unique(): static;

    public function unsigned(): static;

    public function index(): static;

    public function after(string $column): static;

    public function toDefinition(): array;
}
