<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Results;

use ArrayAccess;
use Horizon\Contracts\Support\Arrayable;
use Horizon\Contracts\Support\Jsonable;
use JsonException;

/**
 * Database row returned by table-based QueryBuilder queries.
 *
 * Halcyon will hydrate model objects later. Until then, table queries return a
 * named row object instead of anonymous stdClass instances.
 *
 * @implements ArrayAccess<string, mixed>
 */
final class QueryRow implements ArrayAccess, Arrayable, Jsonable
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private array $attributes = [],
    ) {}

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->attributes, JSON_THROW_ON_ERROR);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) ? $this->get($offset) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! is_string($offset)) {
            return;
        }

        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset)) {
            unset($this->attributes[$offset]);
        }
    }
}
