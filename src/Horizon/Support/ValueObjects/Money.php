<?php

declare(strict_types=1);

namespace Horizon\Support\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(
        private int|float|string $amount,
        private string           $currency,
    ) {}

    public static function of(int|float|string $amount, string $currency = 'USD'): Money
    {
        return new Money($amount, strtoupper($currency));
    }

    public static function fromCents(int $cents, string $currency = 'USD'): Money
    {
        return new Money($cents, strtoupper($currency));
    }

    public static function fromFloat(float $amount, string $currency = 'USD'): Money
    {
        return new Money($amount, strtoupper($currency));
    }

    public static function fromString(string $amount, string $currency = 'USD'): Money
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("Invalid amount: $amount");
        }

        return new Money($amount, strtoupper($currency));
    }

    public function amount(): int|float|string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function toCents(): int
    {
        return (int) round((float) $this->amount * 100);
    }

    public function toFloat(): float
    {
        return (float) $this->amount / (is_int($this->amount) ? 100 : 1);
    }

    public function toString(): string
    {
        return number_format($this->toFloat(), 2) . ' ' . $this->currency;
    }

    public function add(Money $other): Money
    {
        $this->assertSameCurrency($other);
        return new Money((float) $this->amount + (float) $other->amount, $this->currency);
    }

    public function subtract(Money $other): Money
    {
        $this->assertSameCurrency($other);
        return new Money((float) $this->amount - (float) $other->amount, $this->currency);
    }

    public function multiply(int|float $factor): Money
    {
        return new Money((float) $this->amount * $factor, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount == $other->amount
            && $this->currency === $other->currency;
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return (float) $this->amount > (float) $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return (float) $this->amount < (float) $other->amount;
    }

    public function convertTo(string $currency, float $rate): Money
    {
        return new Money(
            round((float) $this->amount * $rate, 2),
            strtoupper($currency)
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: $this->currency and $other->currency."
            );
        }
    }
}
