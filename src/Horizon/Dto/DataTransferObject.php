<?php

declare(strict_types=1);

namespace Horizon\Dto;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Dto\Collections\DtoCollection;
use JsonException;

class DataTransferObject implements DtoContract
{
    public static function from(array|object $data): static
    {
        /** @var static */
        return (new DtoFactory)->make(static::class, $data);
    }

    public static function collection(iterable $items): DtoCollection
    {
        /** @var DtoCollection<static> */
        return (new DtoFactory)->collection(static::class, $items);
    }

    public function toArray(): array
    {
        return (new DtoSerializer)->toArray($this);
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
