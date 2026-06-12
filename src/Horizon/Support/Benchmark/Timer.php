<?php

declare(strict_types=1);

namespace Horizon\Support\Benchmark;

class Timer
{
    private float $startedAt;

    private ?float $stoppedAt = null;

    private function __construct()
    {
        $this->startedAt = hrtime(true);
    }

    public static function start(): static
    {
        return new static();
    }

    public function stop(): float
    {
        $this->stoppedAt = hrtime(true);
        return $this->elapsed();
    }

    public function elapsed(): float
    {
        $end = $this->stoppedAt ?? hrtime(true);
        return ($end - $this->startedAt) / 1_000_000;
    }

    public function reset(): static
    {
        $this->startedAt = hrtime(true);
        $this->stoppedAt = null;
        return $this;
    }
}
