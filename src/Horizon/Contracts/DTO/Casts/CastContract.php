<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO\Casts;

interface CastContract
{
    public function get(mixed $value): mixed;

    public function set(mixed $value): mixed;
}
