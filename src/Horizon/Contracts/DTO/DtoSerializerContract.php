<?php

declare(strict_types=1);

namespace Horizon\Contracts\DTO;

interface DtoSerializerContract
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(DtoContract $dto): array;
}
