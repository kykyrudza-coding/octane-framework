<?php

declare(strict_types=1);

namespace Horizon\Support\Traits;

class Observable
{
    private array $observers = [];
    private array $originals = [];

    public function observe(string $observer, callable $callback): static
    {
        $this->observers[$observer][] = $callback;

        return $this;
    }

    public function __set(string $property, $value): void
    {
        $old = $this->$property ?? null;
        $this->$property = $value;

        if (isset($this->observers[$property])) {
            foreach ($this->observers[$property] as $callback) {
                $callback($value, $old, $this);
            }
        }
    }

    public function isDirty(string $property): bool
    {
        return ($this->original[$property] ?? null) !== ($this->$property ?? null);
    }

    public function syncOriginal(): static
    {
        foreach (array_keys($this->originals) as $property) {
            $this->originals[$property] = $this->$property ?? null;
        }

        return $this;
    }

    public function getDirty(): array
    {
        $dirty = [];

        foreach (array_keys($this->originals) as $property) {
            if ($this->isDirty($property)) {
                $dirty[$property] = $this->$property ?? null;
            }
        }

        return $dirty;
    }
}
