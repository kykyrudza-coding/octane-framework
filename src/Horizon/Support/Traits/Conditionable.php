<?php

declare(strict_types=1);

namespace Horizon\Support\Traits;

use Closure;

trait Conditionable
{
    public function when(mixed $condition, callable $callback, ?callable $default = null): static
    {
        $condition = $condition instanceof Closure ? $condition($this) : $condition;

        if ($condition) {
            $callback($this);
        } elseif ($default) {
            $default($this);
        }

        return $this;
    }

    public function unless(mixed $condition, callable $callback, ?callable $default = null): static
    {
        return $this->when(! $condition, $callback, $default);
    }
}
