<?php

declare(strict_types=1);

namespace Horizon\Support;

use Carbon\Carbon;

final readonly class CarbonTimestamp
{
    public function __construct(
        private Carbon $carbon,
    ) {}

    public static function parse(string $value): self
    {
        return new self(Carbon::parse($value));
    }

    public function toDateTimeString(): string
    {
        return $this->carbon->toDateTimeString();
    }

    public function getCarbon(): Carbon
    {
        return $this->carbon;
    }

    public function __toString(): string
    {
        return $this->toDateTimeString();
    }
}
