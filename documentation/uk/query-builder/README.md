# QueryBuilder

QueryBuilder - SQL builder і executor framework.

Поточна відповідальність:

- вибір таблиці;
- `select`, `where`, `orWhere`, `whereRaw`;
- `join`;
- `orderBy`, `limit`, `offset`;
- `count`, `exists`;
- `insert`, `create`, `update`, `delete`;
- повернення raw rows через `ItemsList`;
- інтеграція з Halcyon mapper через `QueryResultMapperContract`.

```php
use Horizon\QueryBuilder\QueryBuilderFactory;

$users = app(QueryBuilderFactory::class)
    ->forTable('users')
    ->where('active', 1)
    ->get();
```

Через facade:

```php
QB::table('users')->get();
```

Model target:

```php
QB::for(User::class)->get();
```

Якщо Halcyon mapper зареєстрований, rows перетворюються через Halcyon metadata/hydration. Без mapper повертаються `QueryRow`.

## Конфігурація

`config/query-builder.php`:

```php
return [
    'default_connection' => env('DB_CONNECTION', 'mysql'),

    'debug' => [
        'log_queries' => (bool) env('QUERY_LOG', false),
    ],
];
```

Активні ключі:

- `default_connection`: connection name з `config/database.php`;
- `debug.log_queries`: вмикає query log на connection, який використовує QueryBuilder.

Pagination helpers і fetch mode наразі не є частиною QueryBuilder API, тому вони не документуються як supported config.
