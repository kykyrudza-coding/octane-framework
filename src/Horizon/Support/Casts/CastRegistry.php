<?php

declare(strict_types=1);

namespace Horizon\Support\Casts;

use Horizon\Contracts\Support\Casts\CastableContract;
use Horizon\Contracts\Support\Casts\CastRegistryContract;
use Horizon\Support\Exceptions\CastException;

final class CastRegistry implements CastRegistryContract
{
    /** @var array<string, CastableContract> */
    private array $casts = [];

    public function register(string $type, CastableContract $castable): void
    {
        $this->casts[$type] = $castable;
    }

    public function get(string $type): CastableContract
    {
        if (!isset($this->casts[$type])) {
            throw new CastException(
                "Cast [$type] is not registered."
            );
        }

        return $this->casts[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->casts[$type]);
    }
}
