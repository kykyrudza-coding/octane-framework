<?php

declare(strict_types=1);

namespace Horizon\Contracts\Support\Casts;

interface CastRegistryContract
{
    public function register(string $type, CastableContract $castable): void;

    public function get(string $type): CastableContract;

    public function has(string $type): bool;
}
