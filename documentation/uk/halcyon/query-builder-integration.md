# Halcyon and QueryBuilder

Поточний стан: базова інтеграція реалізована.

Цільова поведінка:

```php
QueryBuilder::table('users')->get();
```

Повертає raw rows.

```php
QueryBuilder::for(User::class)->get();
```

Повертає `ItemsList<User>`.

```php
QueryBuilder::for(User::class)->first();
```

Повертає `?User`.

## Потрібні правки

Вже зроблено:

1. QueryBuilder зберігає model target окремо від table name.
2. `for(User::class)` бере table name з Halcyon metadata `#[Table]`.
3. QueryBuilder використовує нейтральний `QueryResultMapperContract`.
4. Halcyon реалізує mapper через `MetadataRepositoryContract` і `HydratorContract`.
5. `table()` залишається raw mode без hydration.
6. `get()` і `first()` проганяють результат через mapper тільки коли задано model target.

## Поточний flow

```text
QueryBuilder::for(User::class)
  -> Halcyon metadata resolves table
  -> SQL query returns raw rows
  -> rows become QueryRow objects
  -> HalcyonResultMapper converts rows to arrays
  -> Hydrator returns ItemsList<User>
```

Ще не реалізовано:

- `with()` / eager loading;
- lazy loading;
- write-flow через models;
- scopes/observers на рівні QueryBuilder execution.
