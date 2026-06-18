<?php

declare(strict_types=1);

namespace Horizon\Dto\Collections;

use ArrayIterator;
use Horizon\Contracts\DTO\DtoCollectionContract;
use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\Support\Arrayable;
use JsonException;
use Traversable;

/**
 * @template TDto of DtoContract
 *
 * @implements DtoCollectionContract<TDto>
 */
class DtoCollection implements DtoCollectionContract
{
    /**
     * @var array<array-key, TDto>
     */
    private array $items;

    /**
     * @param  iterable<array-key, TDto>  $items
     */
    public function __construct(
        iterable $items = [],
    ) {
        $this->items = is_array($items)
            ? $items
            : iterator_to_array($items);
    }

    public function toArray(): array
    {
        return array_map(
            fn (mixed $item): mixed => $item instanceof Arrayable ? $item->toArray() : $item,
            $this->all(),
        );
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function all(): array
    {
        return $this->items;
    }

    public function first(): ?DtoContract
    {
        $items = $this->all();

        $first = $items[array_key_first($items)] ?? null;

        return $first instanceof DtoContract ? $first : null;
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
