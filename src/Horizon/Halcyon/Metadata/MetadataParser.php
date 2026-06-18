<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Metadata;

use Horizon\Contracts\Halcyon\Metadata\MetadataParserContract;
use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;
use Horizon\Contracts\Halcyon\Relations\RelationContract;
use Horizon\Halcyon\Exceptions\InvalidRelationTypeException;
use Horizon\Halcyon\Exceptions\MissingTableAttributeException;
use Horizon\Halcyon\Model\Attributes\Column;
use Horizon\Halcyon\Model\Attributes\Table;
use Horizon\Halcyon\Relations\BelongsTo;
use Horizon\Halcyon\Relations\BelongsToMany;
use Horizon\Halcyon\Relations\HasMany;
use Horizon\Halcyon\Relations\HasOne;
use Horizon\Halcyon\Relations\Relation;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

final class MetadataParser implements MetadataParserContract
{
    private const array RELATION_TYPES = [
        HasMany::class,
        HasOne::class,
        BelongsTo::class,
        BelongsToMany::class,
    ];

    /**
     * @throws ReflectionException
     *
     * @param class-string $class
     */
    public function parse(string $class): ModelMetadataContract
    {
        $reflection = new ReflectionClass($class);

        return new ModelMetadata(
            class: $class,
            table: $this->parseTable($reflection),
            properties: $this->parseProperties($reflection),
            relations: $this->parseRelations($reflection),
            casts: $this->callStaticArray($reflection, 'casts'),
            hidden: $this->callStaticArray($reflection, 'hidden'),
            observers: $this->callStaticArray($reflection, 'observers'),
            scopes: $this->callStaticArray($reflection, 'scopes'),
        );
    }

    private function parseTable(ReflectionClass $reflection): string
    {
        $attributes = $reflection->getAttributes(Table::class);

        if (empty($attributes)) {
            throw new MissingTableAttributeException($reflection->getName());
        }

        return $attributes[0]->newInstance()->name;
    }

    private function parseProperties(ReflectionClass $reflection): array
    {
        $properties = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            $columnAttributes = $property->getAttributes(Column::class);
            $columnName = ! empty($columnAttributes)
                ? $columnAttributes[0]->newInstance()->name
                : $this->toSnakeCase($property->getName());

            $properties[$property->getName()] = new PropertyMetadata(
                phpName: $property->getName(),
                columnName: $columnName,
                phpType: $type->getName(),
                nullable: $type->allowsNull(),
                default: $property->hasDefaultValue() ? $property->getDefaultValue() : null,
            );
        }

        return $properties;
    }

    /**
     * @throws ReflectionException
     */
    private function parseRelations(ReflectionClass $reflection): array
    {
        $relations = [];

        foreach ($reflection->getMethods() as $method) {
            if ($method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $returnType = $method->getReturnType();

            if (! $returnType instanceof ReflectionNamedType) {
                continue;
            }

            $returnTypeName = $returnType->getName();

            if (! in_array($returnTypeName, self::RELATION_TYPES, true)) {
                continue;
            }

            $instance = $method->invoke($reflection->newInstanceWithoutConstructor());

            if (! $instance instanceof RelationContract) {
                throw new InvalidRelationTypeException($returnTypeName, $reflection->getName());
            }

            $name = $instance->getName() !== '' ? $instance->getName() : $method->getName();

            $relations[$name] = new RelationMetadata(
                name: $name,
                method: $method->getName(),
                type: $returnTypeName,
                related: $instance->getRelated(),
                foreignKey: $instance->getForeignKey(),
                localKey: $instance->getLocalKey(),
                pivotTable: method_exists($instance, 'getPivotTable') ? $instance->getPivotTable() : null,
            );
        }

        return $relations;
    }

    private function callStaticArray(ReflectionClass $reflection, string $method): array
    {
        if (! $reflection->hasMethod($method)) {
            return [];
        }

        $reflectionMethod = $reflection->getMethod($method);

        if (! $reflectionMethod->isStatic()) {
            return [];
        }

        $value = $reflectionMethod->invoke(null);

        return is_array($value) ? $value : [];
    }

    private function toSnakeCase(string $name): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }
}
