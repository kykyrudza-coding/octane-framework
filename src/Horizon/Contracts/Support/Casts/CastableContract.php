<?php

declare(strict_types=1);

namespace Horizon\Contracts\Support\Casts;

interface CastableContract
{
    public function get(mixed $value): mixed;
    public function set(mixed $value): mixed;
}
