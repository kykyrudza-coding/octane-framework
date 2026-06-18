<?php

declare(strict_types=1);

namespace Horizon\Dto\Metadata;

use Horizon\Contracts\DTO\Casts\CastContract;
use Horizon\Contracts\DTO\DtoCollectionContract;
use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataParserContract;
use Horizon\Dto\Attributes\CastWith;
use Horizon\Dto\Attributes\CollectionOf;
use Horizon\Dto\Attributes\MapFrom;
use Horizon\Dto\Attributes\MapTo;
use Horizon\Dto\Exceptions\DtoMetadataException;
use Horizon\Dto\Exceptions\InvalidDtoTypeException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class DtoMetadataParser implements DtoMetadataParserContract
{
    /**
     * @throws ReflectionException
     */
    public function parse(string $class): DtoMetadata
    {
        if (! is_a($class, DtoContract::class, true)) {
            throw new InvalidDtoTypeException("DTO [$class] must implement DtoContract.");
        }

        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            throw new DtoMetadataException(
                "Failed to inspect DTO [$class].",
                previous: $exception,
            );
        }

        $properties = [];
        $constructorParameters = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $property = $this->propertyFromParameter($reflection, $parameter);
            $properties[$property->getName()] = $property;
            $constructorParameters[] = $property;
        }

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            if (isset($properties[$reflectionProperty->getName()])) {
                continue;
            }

            $property = $this->propertyFromReflectionProperty($reflectionProperty);
            $properties[$property->getName()] = $property;
        }

        return new DtoMetadata($class, $properties, $constructorParameters);
    }

    /**
     * @throws ReflectionException
     */
    private function propertyFromParameter(ReflectionClass $class, ReflectionParameter $parameter): DtoPropertyMetadata
    {
        $property = $class->hasProperty($parameter->getName())
            ? $class->getProperty($parameter->getName())
            : null;

        $type = $this->typeName($parameter->getType());
        $collectionValueType = $this->collectionValueType($parameter, $property);

        return new DtoPropertyMetadata(
            name: $parameter->getName(),
            inputName: $this->inputName($parameter, $property),
            outputName: $this->outputName($parameter, $property),
            type: $type,
            nullable: $parameter->allowsNull(),
            collection: $collectionValueType !== null || $type === 'array' || is_a((string) $type, DtoCollectionContract::class, true),
            collectionValueType: $collectionValueType,
            cast: $this->cast($parameter, $property),
            hasDefaultValue: $parameter->isDefaultValueAvailable(),
            defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            constructorParameter: true,
        );
    }

    private function propertyFromReflectionProperty(ReflectionProperty $property): DtoPropertyMetadata
    {
        $type = $this->typeName($property->getType());
        $collectionValueType = $this->collectionValueType($property);

        return new DtoPropertyMetadata(
            name: $property->getName(),
            inputName: $this->inputName($property),
            outputName: $this->outputName($property),
            type: $type,
            nullable: $property->getType()?->allowsNull() ?? true,
            collection: $collectionValueType !== null || $type === 'array' || is_a((string) $type, DtoCollectionContract::class, true),
            collectionValueType: $collectionValueType,
            cast: $this->cast($property),
            hasDefaultValue: $property->hasDefaultValue(),
            defaultValue: $property->hasDefaultValue() ? $property->getDefaultValue() : null,
            constructorParameter: false,
        );
    }

    private function inputName(ReflectionParameter|ReflectionProperty $target, ?ReflectionProperty $property = null): string
    {
        return $this->attribute($target, MapFrom::class)
            ?->name
            ?? ($property !== null ? $this->attribute($property, MapFrom::class)?->name : null)
            ?? $target->getName();
    }

    private function outputName(ReflectionParameter|ReflectionProperty $target, ?ReflectionProperty $property = null): string
    {
        return $this->attribute($target, MapTo::class)
            ?->name
            ?? ($property !== null ? $this->attribute($property, MapTo::class)?->name : null)
            ?? $target->getName();
    }

    private function collectionValueType(ReflectionParameter|ReflectionProperty $target, ?ReflectionProperty $property = null): ?string
    {
        return $this->attribute($target, CollectionOf::class)
            ?->dto
            ?? ($property !== null ? $this->attribute($property, CollectionOf::class)?->dto : null);
    }

    /**
     * @return class-string<CastContract>|null
     */
    private function cast(ReflectionParameter|ReflectionProperty $target, ?ReflectionProperty $property = null): ?string
    {
        return $this->attribute($target, CastWith::class)
            ?->cast
            ?? ($property !== null ? $this->attribute($property, CastWith::class)?->cast : null);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $attribute
     * @return T|null
     */
    private function attribute(ReflectionParameter|ReflectionProperty $target, string $attribute): ?object
    {
        $attributes = $target->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF);

        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    private function typeName(?ReflectionType $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            $types = array_filter(
                $type->getTypes(),
                static fn (ReflectionNamedType $type): bool => $type->getName() !== 'null',
            );

            if (count($types) === 1) {
                return array_values($types)[0]->getName();
            }

            return implode('|', array_map(
                static fn (ReflectionNamedType $type): string => $type->getName(),
                $types,
            ));
        }

        return (string) $type;
    }
}
