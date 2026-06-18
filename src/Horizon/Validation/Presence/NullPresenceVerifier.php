<?php

declare(strict_types=1);

namespace Horizon\Validation\Presence;

use Horizon\Contracts\Validation\PresenceVerifierContract;

final class NullPresenceVerifier implements PresenceVerifierContract
{
    public function exists(string $table, string $column, mixed $value): bool
    {
        return false;
    }
}
