<?php

declare(strict_types=1);

namespace Horizon\Dto;

use Horizon\Contracts\DTO\Casts\CastContract;
use Horizon\Contracts\DTO\DtoCollectionContract;
use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\DtoMapperContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataRepositoryContract;
use Horizon\Contracts\DTO\Metadata\DtoPropertyMetadataContract;
use Horizon\Dto\Collections\DtoCollection;
use Horizon\Dto\Exceptions\DtoMappingException;
use Horizon\Dto\Exceptions\MissingDtoPropertyException;
use Horizon\Dto\Metadata\DtoMetadataRepository;
use ReflectionClass;
use ReflectionException;
use Throwable;

class DtoMapper implements DtoMapperContract
{
    private DtoMetadataRepositoryContract $metadata;

    public function __construct(
        ?DtoMetadataRepositoryContract $metadata = null,
        private readonly bool $strict = true,
        private readonly string $unknownFields = 'ignore',
        private readonly string $missingFields = 'throw',
    ) {
        $this->metadata = $metadata ?? new DtoMetadataRepository;
    }

    /**
     * @throws ReflectionException
     */
    public function map(DtoMetadataContract $metadata, array|object $data): DtoContract
    {
        $values = $this->normalizeData($data);
        $this->guardUnknownFields($metadata, $values);

        $arguments = [];

        foreach ($metadata->getConstructorParameters() as $property) {
            $arguments[$property->getName()] = $this->resolveValue($property, $values);
        }

        $class = $metadata->getClass();

        try {
            $dto = new $class(...$arguments);
        } catch (Throwable $exception) {
            throw new DtoMappingException(
                "Failed to instantiate DTO [$class].",
                previous: $exception,
            );
        }

        $this->fillPublicProperties($dto, $metadata, $values);

        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeData(array|object $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof DtoContract) {
            return $data->toArray();
        }

        if (method_exists($data, 'toArray')) {
            $array = $data->toArray();

            if (is_array($array)) {
                return $array;
            }
        }

        return get_object_vars($data);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ReflectionException
     */
    private function resolveValue(DtoPropertyMetadataContract $property, array $data): mixed
    {
        if ($this->hasInputValue($data, $property->getInputName())) {
            return $this->transformValue(
                $property,
                $this->getInputValue($data, $property->getInputName()),
            );
        }

        if ($property->hasDefaultValue()) {
            return $property->getDefaultValue();
        }

        if ($property->isNullable()) {
            return null;
        }

        if (! $this->strict || $this->missingFields === 'null') {
            return null;
        }

        throw new MissingDtoPropertyException(
            "Missing required DTO property [{$property->getInputName()}].",
        );
    }

    /**
     * @throws ReflectionException
     */
    private function transformValue(DtoPropertyMetadataContract $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($property->getCast() !== null) {
            $value = $this->resolveCast($property->getCast())->get($value);
        }

        if ($property->isCollection()) {
            return $this->mapCollection($property, $value);
        }

        $type = $property->getType();

        if (is_string($type) && is_a($type, DtoContract::class, true) && ! $value instanceof $type) {
            if (! is_array($value) && ! is_object($value)) {
                throw new DtoMappingException("Nested DTO [{$property->getName()}] must be mapped from an array or object.");
            }

            return $this->map($this->metadata()->for($type), $value);
        }

        return $value;
    }

    /**
     * @throws ReflectionException
     */
    private function mapCollection(DtoPropertyMetadataContract $property, mixed $value): array|DtoCollectionContract
    {
        if (! is_iterable($value)) {
            throw new DtoMappingException("DTO collection property [{$property->getName()}] must be iterable.");
        }

        $items = [];
        $valueType = $property->getCollectionValueType();

        foreach ($value as $key => $item) {
            $items[$key] = $this->mapCollectionItem($valueType, $item);
        }

        if ($property->getType() === 'array') {
            return $items;
        }

        return new DtoCollection($items);
    }

    /**
     * @throws ReflectionException
     */
    private function mapCollectionItem(?string $valueType, mixed $item): mixed
    {
        if ($valueType === null || ! is_a($valueType, DtoContract::class, true) || $item instanceof $valueType) {
            return $item;
        }

        if (! is_array($item) && ! is_object($item)) {
            throw new DtoMappingException("DTO collection item [$valueType] must be mapped from an array or object.");
        }

        return $this->map($this->metadata()->for($valueType), $item);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ReflectionException
     */
    private function fillPublicProperties(DtoContract $dto, DtoMetadataContract $metadata, array $data): void
    {
        try {
            $reflection = new ReflectionClass($dto);
        } catch (ReflectionException $exception) {
            throw new DtoMappingException(
                "Failed to inspect DTO [{$metadata->getClass()}].",
                previous: $exception,
            );
        }

        foreach ($metadata->getProperties() as $property) {
            if ($property->isConstructorParameter()) {
                continue;
            }

            if (! $this->hasInputValue($data, $property->getInputName())) {
                continue;
            }

            $this->setProperty(
                $dto,
                $reflection,
                $property,
                $this->transformValue($property, $this->getInputValue($data, $property->getInputName())),
            );
        }
    }

    /**
     * @throws ReflectionException
     */
    private function setProperty(
        DtoContract $dto,
        ReflectionClass $reflection,
        DtoPropertyMetadataContract $property,
        mixed $value,
    ): void {
        if (! $reflection->hasProperty($property->getName())) {
            return;
        }

        $reflectionProperty = $reflection->getProperty($property->getName());

        if (! $reflectionProperty->isPublic()) {
            return;
        }

        try {
            $reflectionProperty->setValue($dto, $value);
        } catch (Throwable $exception) {
            throw new DtoMappingException(
                "Failed to set DTO property [{$property->getName()}].",
                previous: $exception,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasInputValue(array $data, string $key): bool
    {
        if (array_key_exists($key, $data)) {
            return true;
        }

        return str_contains($key, '.') && $this->hasNestedInputValue($data, $key);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function getInputValue(array $data, string $key): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        $value = $data;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasNestedInputValue(array $data, string $key): bool
    {
        $value = $data;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    /**
     * @param  class-string<CastContract>  $cast
     */
    private function resolveCast(string $cast): CastContract
    {
        $instance = new $cast;

        if (! $instance instanceof CastContract) {
            throw new DtoMappingException("DTO cast [$cast] must implement CastContract.");
        }

        return $instance;
    }

    private function metadata(): DtoMetadataRepositoryContract
    {
        return $this->metadata;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardUnknownFields(DtoMetadataContract $metadata, array $data): void
    {
        if (! $this->strict || $this->unknownFields !== 'throw') {
            return;
        }

        $known = [];

        foreach ($metadata->getProperties() as $property) {
            $inputName = $property->getInputName();
            $known[] = $inputName;

            if (str_contains($inputName, '.')) {
                $known[] = explode('.', $inputName, 2)[0];
            }
        }

        foreach (array_keys($data) as $key) {
            if (! in_array($key, $known, true)) {
                throw new DtoMappingException("Unknown DTO field [$key].");
            }
        }
    }
}
