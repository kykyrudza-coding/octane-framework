# Database: поточний стан

У поточному `octane-framework` немає реалізації:

- database connection manager;
- query builder;
- ORM;
- migrations runner;
- schema builder;
- transactions;
- working `DB` facade.

`octane-application/config/database.php` є лише configuration-заготовкою, а
`db/Core/0000_00_00_create_session_table.php` не містить migration code.
Клас `Horizon\Support\Facades\DB` порожній.

Тому приклади на кшталт `DB::table('users')->get()` для цієї версії
некоректні.

До появи database component застосунок може:

1. зареєструвати PDO або сторонній database client у service provider;
2. створити власні repository classes;
3. inject-ити repository contracts у controllers/services через container.

```php
$this->app->singleton(PDO::class, function () {
    return new PDO(
        env('DB_DSN'),
        env('DB_USERNAME'),
        env('DB_PASSWORD'),
    );
});
```
