<?php

declare(strict_types=1);

namespace Horizon\Support\ValueObjects;

use InvalidArgumentException;

final readonly class Interval
{
    private function __construct(
        private int $seconds,
    ) {}

    public static function seconds(int $seconds): Interval
    {
        return new Interval($seconds);
    }

    public static function minutes(int $minutes): Interval
    {
        return new Interval($minutes * 60);
    }

    public static function hours(int $hours): Interval
    {
        return new Interval($hours * 3600);
    }

    public static function days(int $days): Interval
    {
        return new Interval($days * 86400);
    }

    public static function weeks(int $weeks): Interval
    {
        return new Interval($weeks * 604800);
    }

    public static function months(int $months): Interval
    {
        return new Interval($months * 2592000);
    }

    public static function fromString(string $interval): Interval
    {
        preg_match('/^(\d+)\s*(second|minute|hour|day|week|month)s?$/', $interval, $matches);

        if (empty($matches)) {
            throw new InvalidArgumentException("Invalid interval format: $interval");
        }

        $value = (int) $matches[1];

        return match($matches[2]) {
            'second' => Interval::seconds($value),
            'minute' => Interval::minutes($value),
            'hour'   => Interval::hours($value),
            'day'    => Interval::days($value),
            'week'   => Interval::weeks($value),
            'month'  => Interval::months($value),
        };
    }

    public function toSeconds(): int
    {
        return $this->seconds;
    }

    public function toMinutes(): float
    {
        return $this->seconds / 60;
    }

    public function toHours(): float
    {
        return $this->seconds / 3600;
    }

    public function toDays(): float
    {
        return $this->seconds / 86400;
    }

    public function add(Interval $other): Interval
    {
        return new Interval($this->seconds + $other->seconds);
    }

    public function subtract(Interval $other): Interval
    {
        return new Interval($this->seconds - $other->seconds);
    }

    public function multiply(int $factor): Interval
    {
        return new Interval($this->seconds * $factor);
    }

    public function isGreaterThan(Interval $other): bool
    {
        return $this->seconds > $other->seconds;
    }

    public function isLessThan(Interval $other): bool
    {
        return $this->seconds < $other->seconds;
    }

    public function equals(Interval $other): bool
    {
        return $this->seconds === $other->seconds;
    }

    public function __toString(): string
    {
        return match(true) {
            $this->seconds < 60     => "$this->seconds seconds",
            $this->seconds < 3600   => (int)($this->seconds / 60) . ' minutes',
            $this->seconds < 86400  => (int)($this->seconds / 3600) . ' hours',
            default                 => (int)($this->seconds / 86400) . ' days',
        };
    }
}
