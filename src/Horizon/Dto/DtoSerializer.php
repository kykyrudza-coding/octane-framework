<?php

declare(strict_types=1);

namespace Horizon\Dto;

use Horizon\Contracts\DTO\Casts\CastContract;
use Horizon\Contracts\DTO\DtoCollectionContract;
use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\DtoSerializerContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataRepositoryContract;
use Horizon\Contracts\Support\Arrayable;
use Horizon\Dto\Exceptions\DtoSerializationException;
use Horizon\Dto\Metadata\DtoMetadataRepository;
use ReflectionClass;
use ReflectionException;

class DtoSerializer implements DtoSerializerContract
{
    private DtoMetadataRepositoryContract $metadata;

    public function __construct(
        ?DtoMetadataRepositoryContract $metadata = null,
        private readonly bool $includeNull = true,
    ) {
        $this->metadata = $metadata ?? new DtoMetadataRepository;
    }

    /**
     * @throws ReflectionException
     */
    public function toArray(DtoContract $dto): array
    {
        $metadata = $this->metadata()->for($dto::class);

        try {
            $reflection = new ReflectionClass($dto);
        } catch (ReflectionException $exception) {
            $class = $dto::class;

            throw new DtoSerializationException(
                "Failed to inspect DTO [$class].",
                previous: $exception,
            );
        }

        $result = [];

        foreach ($metadata->getProperties() as $property) {
            if (! $reflection->hasProperty($property->getName())) {
                continue;
            }

            $reflectionProperty = $reflection->getProperty($property->getName());

            if (! $reflectionProperty->isPublic() || ! $reflectionProperty->isInitialized($dto)) {
                continue;
            }

            $value = $this->serializeValue(
                $property->getCast(),
                $reflectionProperty->getValue($dto),
            );

            if ($value === null && ! $this->includeNull) {
                continue;
            }

            $result[$property->getOutputName()] = $value;
        }

        return $result;
    }

    private function serializeValue(?string $cast, mixed $value): mixed
    {
        if ($cast !== null) {
            $value = $this->resolveCast($cast)->set($value);
        }

        if ($value instanceof DtoContract) {
            return $this->toArray($value);
        }

        if ($value instanceof DtoCollectionContract) {
            return $value->toArray();
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->serializeValue(null, $item), $value);
        }

        return $value;
    }

    /**
     * @param  class-string<CastContract>  $cast
     */
    private function resolveCast(string $cast): CastContract
    {
        $instance = new $cast;

        if (! $instance instanceof CastContract) {
            throw new DtoSerializationException("DTO cast [$cast] must implement CastContract.");
        }

        return $instance;
    }

    private function metadata(): DtoMetadataRepositoryContract
    {
        return $this->metadata;
    }
}
