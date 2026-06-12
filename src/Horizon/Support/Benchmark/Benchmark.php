<?php

declare(strict_types=1);

namespace Horizon\Support\Benchmark;

final class Benchmark
{
    public static function measure(callable $callback, int $iterations = 1): BenchmarkResult
    {
        $measurements = [];

        for ($i = 0; $i < $iterations; $i++) {
            $timer = Timer::start();
            $callback();
            $measurements[] = $timer->stop();
        }

        return new BenchmarkResult($measurements, $iterations);
    }

    public static function dd(callable $callback, int $iterations = 1): never
    {
        $result = static::measure($callback, $iterations);
        dd($result);
    }
}
