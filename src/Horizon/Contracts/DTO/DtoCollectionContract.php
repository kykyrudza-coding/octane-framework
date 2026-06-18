<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO;

use Countable;
use Horizon\Contracts\Support\Arrayable;
use Horizon\Contracts\Support\Jsonable;
use IteratorAggregate;

/**
 * @template TDto of DtoContract
 *
 * @extends IteratorAggregate<array-key, TDto>
 */
interface DtoCollectionContract extends Arrayable, Countable, IteratorAggregate, Jsonable
{
    /**
     * @return array<array-key, TDto>
     */
    public function all(): array;

    /**
     * @return TDto|null
     */
    public function first(): ?DtoContract;
}
