<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

interface PresenceVerifierContract
{
    public function exists(string $table, string $column, mixed $value): bool;
}
