# Database

Database package містить connection manager, connection factory, PDO-backed drivers, schema builder, migrations і seeding commands.

`config/database.php`:

```php
return [
    'default_connection' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 3306),
            'database' => env('DB_NAME', 'octane'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'options' => [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 5432),
            'database' => env('DB_NAME', 'octane'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'schema' => 'public',
            'options' => [],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_NAME', 'database.sqlite'),
            'options' => [],
        ],
    ],

    'query_log' => [
        'enabled' => (bool) env('DB_QUERY_LOG', false),
        'slow_threshold' => (int) env('DB_QUERY_LOG_SLOW_MS', 100),
    ],
];
```

Активні keys:

- `default_connection`;
- `connections.*`;
- `query_log.enabled`.

`query_log.slow_threshold` збережений як application setting, але поточний `Connection` тільки записує `time_ms` для кожного query і не фільтрує slow queries.

## Connections

```php
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;

$manager = app(ConnectionManagerContract::class);
$connection = $manager->connection();

$rows = $connection->select('select * from users where active = ?', [1]);
```

Підтримані drivers:

- `mysql`;
- `pgsql`;
- `sqlite`.

SQLite database path може бути абсолютним, `:memory:` або відносним до application database path.

## Migrations

Database provider реєструє console commands:

- `migrate`;
- `migrate:rollback`;
- `migrate:fresh`;
- `migrate:reset`;
- `make:migration`;
- `db:seed`.

Schema builder має компілятори для MySQL, PostgreSQL і SQLite.
