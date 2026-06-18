# Halcyon

Halcyon - ORM-шар Octane для опису моделей, metadata та hydration.

Це не Active Record. Модель не виконує запити і не має методів `save()`, `delete()` або `query()`.

Основний поділ відповідальності:

- QueryBuilder будує SQL і повертає raw rows.
- Halcyon читає metadata моделі.
- Hydrator перетворює рядки бази даних у model objects.

## Стан

Реалізовано:

- базова модель `Horizon\Halcyon\Model\Model`;
- атрибути `#[Table]` і `#[Column]`;
- metadata parser/repository/cache;
- file metadata cache;
- hydration в `ItemsList`;
- built-in casts для `CarbonTimestamp`, `CarbonDateTime`, backed enums;
- relation descriptors `HasMany`, `HasOne`, `BelongsTo`, `BelongsToMany`;
- сервіс `Halcyon` для observers, scopes і morph map.

Ще не реалізовано:

- інтеграцію `QueryBuilder::for(Model::class)` з Hydrator;
- eager loading;
- lazy loading;
- persistence API.

Детальніше:

- [Metadata](metadata.md)
- [Hydration](hydration.md)
- [Relations](relations.md)
- [QueryBuilder integration](query-builder-integration.md)
