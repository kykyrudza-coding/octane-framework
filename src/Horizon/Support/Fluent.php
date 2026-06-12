<?php

declare(strict_types=1);

namespace Horizon\Support;

use Horizon\Contracts\Support\Arrayable;
use Horizon\Contracts\Support\Jsonable;

class Fluent implements Arrayable, Jsonable
{

    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set($key, $value = null): Fluent
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->attributes);
    }
}
