<?php

declare(strict_types=1);

namespace Horizon\Support;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

class ItemsList implements ArrayAccess, Countable, IteratorAggregate
{

    public function offsetExists(mixed $offset): bool
    {
        //
    }

    public function offsetGet(mixed $offset): mixed
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset(mixed $offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    public function count(): int
    {
        // TODO: Implement count() method.
    }

    public function getIterator(): Traversable
    {
        // TODO: Implement getIterator() method.
    }
}
