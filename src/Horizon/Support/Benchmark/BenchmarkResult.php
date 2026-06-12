<?php

declare(strict_types=1);

namespace Horizon\Support\Benchmark;

use Horizon\Contracts\Support\Arrayable;

final readonly class BenchmarkResult implements Arrayable
{
    public function __construct(
        private array $measurements,
        private int $iterations,
    ) {}

    public function average(): float
    {
        return array_sum($this->measurements) / array_sum($this->iterations);
    }

    public function min(): float
    {
        return min($this->measurements);
    }

    public function max(): float
    {
        return max($this->measurements);
    }

    public function total(): float
    {
        return array_sum($this->measurements);
    }

    public function iterations(): int
    {
        return $this->iterations;
    }

    public function toArray(): array
    {
        return [
            'average' => $this->average(),
            'min' => $this->min(),
            'max' => $this->max(),
            'total' => $this->total(),
            'iterations' => $this->iterations(),
        ];
    }
}
