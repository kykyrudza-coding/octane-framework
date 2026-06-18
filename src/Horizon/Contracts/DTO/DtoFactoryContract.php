<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO;

interface DtoFactoryContract
{
    /**
     * @template TDto of DtoContract
     *
     * @param  class-string<TDto>  $dto
     * @return TDto
     */
    public function make(string $dto, array|object $data): DtoContract;

    /**
     * @template TDto of DtoContract
     *
     * @param  class-string<TDto>  $dto
     * @param  iterable<array-key, array|object>  $items
     * @return DtoCollectionContract<TDto>
     */
    public function collection(string $dto, iterable $items): DtoCollectionContract;
}
