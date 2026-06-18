<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Hydration;

use BackedEnum;
use Horizon\Contracts\Halcyon\Hydration\Casts\CastContract;
use Horizon\Contracts\Halcyon\Hydration\HydratorContract;
use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;
use Horizon\Contracts\Halcyon\Metadata\PropertyMetadataContract;
use Horizon\Contracts\Halcyon\Model\ModelContract;
use Horizon\Halcyon\Exceptions\HydrationException;
use Horizon\Halcyon\Exceptions\InvalidCastException;
use Horizon\Halcyon\Hydration\Casts\BackedEnumCast;
use Horizon\Halcyon\Hydration\Casts\CarbonDateTimeCast;
use Horizon\Halcyon\Hydration\Casts\CarbonTimestampCast;
use Horizon\Support\CarbonDateTime;
use Horizon\Support\CarbonTimestamp;
use Horizon\Support\ItemsList;
use ReflectionClass;
use ReflectionException;
use Throwable;

final class Hydrator implements HydratorContract
{
    /**
     * @throws ReflectionException
     */
    public function hydrate(ModelMetadataContract $metadata, array $rows): ItemsList
    {
        $models = [];

        foreach ($rows as $row) {
            $models[] = $this->hydrateOne($metadata, $row);
        }

        return new ItemsList($models);
    }

    /**
     * @throws ReflectionException
     */
    public function hydrateOne(ModelMetadataContract $metadata, array $row): ModelContract
    {
        $class = $metadata->getClass();
        $model = new ReflectionClass($class)->newInstanceWithoutConstructor();

        foreach ($metadata->getProperties() as $property) {
            try {
                $value = $row[$property->getColumnName()] ?? null;
                $model->{$property->getPhpName()} = $this->castGet(
                    value: $value,
                    property: $property,
                    casts: $metadata->getCasts(),
                    model: $class,
                );
            } catch (HydrationException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw new HydrationException($class, $property->getPhpName(), $e);
            }
        }

        return $model;
    }

    private function castGet(
        mixed $value,
        PropertyMetadataContract $property,
        array $casts,
        string $model,
    ): mixed {
        $phpName = $property->getPhpName();
        $phpType = $property->getPhpType();

        // 1. явний cast з casts()
        if (isset($casts[$phpName])) {
            $castClass = $casts[$phpName];
            $cast = new $castClass();

            if (! $cast instanceof CastContract) {
                throw new InvalidCastException($castClass, $phpName, $model);
            }

            return $cast->get($value);
        }

        // 2. вбудовані типи
        if ($phpType === CarbonTimestamp::class) {
            return new CarbonTimestampCast()->get($value);
        }

        if ($phpType === CarbonDateTime::class) {
            return new CarbonDateTimeCast()->get($value);
        }

        if ($this->isBackedEnum($phpType)) {
            return new BackedEnumCast($phpType)->get($value);
        }

        // 3. scalar — PHP typed property сам кастить
        return $value;
    }

    public function castSet(
        mixed $value,
        string $phpName,
        string $phpType,
        array $casts,
        string $model,
    ): mixed {
        // 1. явний cast
        if (isset($casts[$phpName])) {
            $castClass = $casts[$phpName];
            $cast = new $castClass();

            if (! $cast instanceof CastContract) {
                throw new InvalidCastException($castClass, $phpName, $model);
            }

            return $cast->set($value);
        }

        // 2. вбудовані типи
        if ($phpType === CarbonTimestamp::class) {
            return new CarbonTimestampCast()->set($value);
        }

        if ($phpType === CarbonDateTime::class) {
            return new CarbonDateTimeCast()->set($value);
        }

        if ($this->isBackedEnum($phpType)) {
            return new BackedEnumCast($phpType)->set($value);
        }

        // 3. scalar
        return $value;
    }

    private function isBackedEnum(string $type): bool
    {
        return enum_exists($type) && is_a($type, BackedEnum::class, true);
    }
}
