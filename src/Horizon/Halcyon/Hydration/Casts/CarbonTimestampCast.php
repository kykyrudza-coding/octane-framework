<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Hydration\Casts;

use Horizon\Contracts\Halcyon\Hydration\Casts\CastContract;
use Horizon\Support\CarbonTimestamp;

final class CarbonTimestampCast implements CastContract
{
    public function get(mixed $value): ?CarbonTimestamp
    {
        if ($value === null) {
            return null;
        }

        return CarbonTimestamp::parse($value);
    }

    public function set(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CarbonTimestamp) {
            return $value->toDateTimeString();
        }

        return (string) $value;
    }
}
