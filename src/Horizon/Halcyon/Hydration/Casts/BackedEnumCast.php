<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Hydration\Casts;

use BackedEnum;
use Horizon\Contracts\Halcyon\Hydration\Casts\CastContract;

final readonly class BackedEnumCast implements CastContract
{
    public function __construct(
        private string $enumClass,
    ) {}

    public function get(mixed $value): ?BackedEnum
    {
        if ($value === null) {
            return null;
        }

        return $this->enumClass::from($value);
    }

    public function set(mixed $value): int|string|null
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}
