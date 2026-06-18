# QueryBuilder

QueryBuilder - SQL builder і executor framework.

Поточна відповідальність:

- вибір таблиці;
- `select`, `where`, `orWhere`, `whereRaw`;
- `join`;
- `orderBy`, `limit`, `offset`;
- `count`, `exists`;
- `insert`, `create`, `update`, `delete`;
- повернення raw rows через `ItemsList`.

QueryBuilder не є ORM і не має знати деталі Halcyon-моделей.

Інтеграція з Halcyon відбувається через `QueryResultMapperContract`, а не через прямий dependency на ORM-класи.

```php
QueryBuilder::table('users')->get(); // ItemsList<QueryRow>
QueryBuilder::for(User::class)->get(); // ItemsList<User>, якщо Halcyon mapper зареєстрований
```
