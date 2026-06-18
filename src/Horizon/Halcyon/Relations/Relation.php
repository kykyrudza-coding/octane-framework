<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Relations;

final class Relation
{
    public static function hasMany(
        string $related,
        string $foreignKey,
        string $localKey,
        ?string $name = null,
    ): HasMany {
        return new HasMany(
            name: $name ?? '',
            related: $related,
            foreignKey: $foreignKey,
            localKey: $localKey,
        );
    }

    public static function hasOne(
        string $related,
        string $foreignKey,
        string $localKey,
        ?string $name = null,
    ): HasOne {
        return new HasOne(
            name: $name ?? '',
            related: $related,
            foreignKey: $foreignKey,
            localKey: $localKey,
        );
    }

    public static function belongsTo(
        string $related,
        string $foreignKey,
        string $localKey,
        ?string $name = null,
    ): BelongsTo {
        return new BelongsTo(
            name: $name ?? '',
            related: $related,
            foreignKey: $foreignKey,
            localKey: $localKey,
        );
    }

    public static function belongsToMany(
        string $related,
        string $pivotTable,
        string $foreignKey,
        string $localKey,
        ?string $name = null,
    ): BelongsToMany {
        return new BelongsToMany(
            name: $name ?? '',
            related: $related,
            pivotTable: $pivotTable,
            foreignKey: $foreignKey,
            localKey: $localKey,
        );
    }
}
