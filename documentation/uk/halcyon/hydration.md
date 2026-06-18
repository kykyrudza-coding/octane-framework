# Halcyon Hydration

Hydrator перетворює raw database rows у model objects.

```php
$items = $hydrator->hydrate($metadata, [
    ['id' => 1, 'name' => 'Test'],
]);
```

Повертається `Horizon\Support\ItemsList`.

Hydration rules:

- property metadata визначає PHP property і database column;
- explicit casts з `casts()` мають пріоритет;
- `CarbonTimestamp` і `CarbonDateTime` підтримуються вбудовано;
- backed enum підтримується через `BackedEnumCast`;
- невдала hydration обгортається в `HydrationException`.

`castSet()` існує для майбутнього write-flow, але persistence API ще не реалізований.
