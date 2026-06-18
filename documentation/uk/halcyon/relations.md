# Halcyon Relations

Relations у Halcyon - це metadata descriptors, а не lazy database calls.

```php
protected function posts(): HasMany
{
    return Relation::hasMany(
        related: Post::class,
        foreignKey: 'user_id',
        localKey: 'id',
    );
}
```

Назва relation береться з method name (`posts`), якщо явно не передати `name`.

Підтримані descriptors:

- `HasMany`
- `HasOne`
- `BelongsTo`
- `BelongsToMany`

`BelongsToMany` додатково зберігає `pivotTable` у relation metadata.

Lazy loading ще не реалізований. Якщо relation не завантажено, `Model::getRelation()` кидає `RelationNotLoadedException`.
