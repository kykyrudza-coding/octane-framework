<?php

declare(strict_types=1);

namespace Horizon\Support\Traits;

trait Observable
{
    private array $observers = [];

    private array $originals = [];

    private array $values = [];

    public function observe(string $property, callable $callback): static
    {
        $this->observers[$property][] = $callback;
        $this->originals[$property] = $this->values[$property] ?? null;

        return $this;
    }

    public function __set(string $property, $value): void
    {
        $old = $this->values[$property] ?? null;
        $this->values[$property] = $value;

        if (isset($this->observers[$property])) {
            foreach ($this->observers[$property] as $callback) {
                $callback($value, $old, $this);
            }
        }
    }

    public function __get(string $property): mixed
    {
        return $this->values[$property] ?? null;
    }

    public function __isset(string $property): bool
    {
        return array_key_exists($property, $this->values);
    }

    public function isDirty(string $property): bool
    {
        return ($this->originals[$property] ?? null) !== ($this->values[$property] ?? null);
    }

    public function syncOriginal(): static
    {
        $this->originals = $this->values;

        return $this;
    }

    public function getDirty(): array
    {
        $dirty = [];

        foreach (array_keys($this->originals) as $property) {
            if ($this->isDirty($property)) {
                $dirty[$property] = $this->values[$property] ?? null;
            }
        }

        return $dirty;
    }
}
