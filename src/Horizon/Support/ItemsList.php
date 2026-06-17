<?php

declare(strict_types=1);

namespace Horizon\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Horizon\Contracts\Support\Arrayable;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class ItemsList implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(
        protected array $items = [],
    ) {}

    // -------------------------------------------------------------------------
    // Static constructors
    // -------------------------------------------------------------------------

    /**
     * @template T
     * @param array<array-key, T> $items
     * @return static<array-key, T>
     */
    public static function make(array $items = []): static
    {
        return new static($items);
    }

    /**
     * @template T
     * @param iterable<array-key, T> $items
     * @return static<array-key, T>
     */
    public static function from(iterable $items): static
    {
        return new static(
            $items instanceof \Traversable
                ? iterator_to_array($items)
                : $items,
        );
    }

    // -------------------------------------------------------------------------
    // Reads
    // -------------------------------------------------------------------------

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return TValue|null
     */
    public function first(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items[array_key_first($this->items)] ?? null;
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return TValue|null
     */
    public function last(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items[array_key_last($this->items)] ?? null;
        }

        $last = null;

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                $last = $value;
            }
        }

        return $last;
    }

    /**
     * @param TKey $key
     * @param TValue|null $default
     * @return TValue|null
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @param TKey $key
     */
    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param TValue $value
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, strict: true);
    }

    // -------------------------------------------------------------------------
    // Transformation
    // -------------------------------------------------------------------------

    /**
     * @template U
     * @param callable(TValue, TKey): U $callback
     * @return static<TKey, U>
     */
    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * @param callable(TValue, TKey): bool $callback
     * @return static<TKey, TValue>
     */
    public function filter(?callable $callback = null): static
    {
        if ($callback === null) {
            return new static(array_values(array_filter($this->items)));
        }

        return new static(array_values(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH)));
    }

    /**
     * @param callable(TValue, TKey): mixed $callback
     * @return static<array-key, TValue>
     */
    public function sortBy(callable $callback, int $options = SORT_REGULAR, bool $descending = false): static
    {
        $items = $this->items;

        usort($items, fn($a, $b) => $descending
            ? $callback($b, 0) <=> $callback($a, 0)
            : $callback($a, 0) <=> $callback($b, 0),
        );

        return new static($items);
    }

    /**
     * @param callable(TValue, TKey): mixed $callback
     * @return static<mixed, static<TKey, TValue>>
     */
    public function groupBy(callable $callback): static
    {
        $groups = [];

        foreach ($this->items as $key => $value) {
            $groupKey = $callback($value, $key);
            $groups[$groupKey][] = $value;
        }

        return new static(array_map(fn(array $group) => new static($group), $groups));
    }

    /**
     * @param callable(TValue, TKey): array-key $callback
     * @return static<array-key, TValue>
     */
    public function keyBy(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $result[$callback($value, $key)] = $value;
        }

        return new static($result);
    }

    /**
     * @template U
     * @param callable(U, TValue): U $callback
     * @param U $initial
     * @return U
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @return static<int, TValue>
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * @return static<TValue, TKey>
     */
    public function flip(): static
    {
        return new static(array_flip($this->items));
    }

    /**
     * @return static<int, TKey>
     */
    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    /**
     * @param int $size
     * @return static<int, static<TKey, TValue>>
     */
    public function chunk(int $size): static
    {
        return new static(
            array_map(
                fn(array $chunk) => new static($chunk),
                array_chunk($this->items, $size, preserve_keys: true),
            ),
        );
    }

    /**
     * @return static<TKey, TValue>
     */
    public function take(int $limit): static
    {
        if ($limit < 0) {
            return new static(array_slice($this->items, $limit));
        }

        return new static(array_slice($this->items, 0, $limit));
    }

    /**
     * @return static<TKey, TValue>
     */
    public function skip(int $count): static
    {
        return new static(array_slice($this->items, $count));
    }

    /**
     * @param static<TKey, TValue>|array<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function merge(self|array $items): static
    {
        $other = $items instanceof self ? $items->all() : $items;

        return new static(array_merge($this->items, $other));
    }

    /**
     * @param callable(TValue, TKey): void $callback
     * @return $this
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Pluck / column
    // -------------------------------------------------------------------------

    /**
     * @return static<int, mixed>
     */
    public function pluck(string $key): static
    {
        return new static(array_map(
            fn($item) => is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null),
            $this->items,
        ));
    }

    // -------------------------------------------------------------------------
    // Aggregates
    // -------------------------------------------------------------------------

    public function sum(callable|string|null $callback = null): int|float
    {
        $values = $callback === null
            ? $this->items
            : $this->map(is_string($callback) ? fn($v) => $v->{$callback} ?? 0 : $callback)->all();

        return array_sum($values);
    }

    public function avg(callable|string|null $callback = null): int|float
    {
        $count = $this->count();

        return $count > 0 ? $this->sum($callback) / $count : 0;
    }

    public function min(callable|string|null $callback = null): mixed
    {
        $values = $callback !== null
            ? $this->map(is_string($callback) ? fn($v) => $v->{$callback} ?? null : $callback)->all()
            : $this->items;

        return min($values);
    }

    public function max(callable|string|null $callback = null): mixed
    {
        $values = $callback !== null
            ? $this->map(is_string($callback) ? fn($v) => $v->{$callback} ?? null : $callback)->all()
            : $this->items;

        return max($values);
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return array_map(
            fn($item) => $item instanceof Arrayable ? $item->toArray() : $item,
            $this->items,
        );
    }

    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    // -------------------------------------------------------------------------
    // ArrayAccess
    // -------------------------------------------------------------------------

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    // -------------------------------------------------------------------------
    // Countable
    // -------------------------------------------------------------------------

    public function count(): int
    {
        return count($this->items);
    }

    // -------------------------------------------------------------------------
    // IteratorAggregate
    // -------------------------------------------------------------------------

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
