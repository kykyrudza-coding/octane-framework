<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Hydration\Casts;

use Horizon\Contracts\Halcyon\Hydration\Casts\CastContract;
use Horizon\Support\CarbonDateTime;

final class CarbonDateTimeCast implements CastContract
{
    public function get(mixed $value): ?CarbonDateTime
    {
        if ($value === null) {
            return null;
        }

        return CarbonDateTime::parse($value);
    }

    public function set(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CarbonDateTime) {
            return $value->toDateTimeString();
        }

        return (string) $value;
    }
}
