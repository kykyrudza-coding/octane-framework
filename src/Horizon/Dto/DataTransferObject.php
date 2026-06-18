<?php

declare(strict_types=1);

namespace Horizon\Dto;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\DTO\DtoSerializerContract;
use Horizon\Dto\Collections\DtoCollection;
use JsonException;
use Throwable;

class DataTransferObject implements DtoContract
{
    public static function from(array|object $data): static
    {
        /** @var static */
        return self::factory()->make(static::class, $data);
    }

    public static function collection(iterable $items): DtoCollection
    {
        /** @var DtoCollection<static> */
        return self::factory()->collection(static::class, $items);
    }

    public function toArray(): array
    {
        return $this->serializer()->toArray($this);
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        $flags = JSON_THROW_ON_ERROR;

        try {
            $configured = \config('dto.serialization.json_flags', $flags);
            $flags = is_int($configured) ? $configured : $flags;
        } catch (Throwable) {
            //
        }

        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    private static function factory(): DtoFactoryContract
    {
        try {
            $factory = \app(DtoFactoryContract::class);

            if ($factory instanceof DtoFactoryContract) {
                return $factory;
            }
        } catch (Throwable) {
            //
        }

        return new DtoFactory;
    }

    private function serializer(): DtoSerializerContract
    {
        try {
            $serializer = \app(DtoSerializerContract::class);

            if ($serializer instanceof DtoSerializerContract) {
                return $serializer;
            }
        } catch (Throwable) {
            //
        }

        return new DtoSerializer;
    }
}
