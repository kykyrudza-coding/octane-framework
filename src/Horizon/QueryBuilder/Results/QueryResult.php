<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Results;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Lightweight list returned by QueryBuilder::get().
 *
 * Intentionally minimal for Milestone 1. Will grow toward a full
 * ItemsList abstraction with map/filter/etc. in later milestones.
 *
 * @template T of object
 * @implements IteratorAggregate<int, T>
 */
final readonly class QueryResult implements Countable, IteratorAggregate
{
    /** @param list<T> $items */
    public function __construct(
        private array $items = [],
    ) {}

    /** @return list<T> */
    public function all(): array
    {
        return $this->items;
    }

    /** @return T|null */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
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
     * @template U
     * @param callable(T): U $callback
     * @return list<U>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    /**
     * @param callable(T): bool $callback
     * @return self<T>
     */
    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->items, $callback)));
    }

    /** @return Traversable<int, T> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(fn($item) => (array) $item, $this->items);
    }
}
