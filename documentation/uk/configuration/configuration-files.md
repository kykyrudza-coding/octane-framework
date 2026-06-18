# Конфігураційні файли

Під час bootstrap framework завантажує всі PHP-файли з `config/`. Назва файлу стає верхнім ключем repository:

```php
// config/app.php
return [
    'name' => env('APP_NAME', 'Octane'),
    'debug' => (bool) env('APP_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
];
```

Читання відбувається через dot notation:

```php
$name = config('app.name');
$timezone = config('app.timezone', 'UTC');
```

Або напряму через контракт:

```php
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;

$config = app(ConfigRepositoryContract::class);
$all = $config->all();
```

`ConfigRepository::get()` підтримує вкладені ключі й коректно повертає `null`, якщо `null` задано явно в конфігу. `ConfigRepository::set()` встановлює top-level ключ:

```php
$config->set('feature', ['enabled' => true]);
```

`has()` перевіряє тільки top-level ключі; для вкладених значень використовуйте `get()`.

## Джерело істини

Після версії `0.2.0` конфіги є основним місцем для параметрів runtime. `boot/app.php` лишається composition entrypoint для речей, які потрібні до завантаження env/config: base path, custom paths і файли середовища.

Активні конфіги skeleton:

| Файл | Що налаштовує |
| --- | --- |
| `app.php` | name/debug/url/timezone/key і application providers |
| `database.php` | connections, default connection, database query log |
| `query-builder.php` | default connection і debug query log для QueryBuilder |
| `routing.php` | route files і group prefix/name defaults |
| `http.php` | dev server, request normalization, middleware groups, JSON response flags |
| `exceptions.php` | debug rendering, ignored exceptions, renderer selection |
| `prism.php` | view path/extensions, compiler cache, components, directives |
| `halcyon.php` | ORM metadata cache, morph map, observers, scopes |
| `dto.php` | DTO metadata cache, mapping, serialization |
| `validation.php` | validator defaults, presence verifier, messages, attributes |
| `hashing.php` | hash driver and bcrypt/argon2 options |
| `docs.php` | API docs route/output/source |
| `console.php` | application command classes |

## Bootstrap пріоритети

Якщо `boot/app.php` явно викликає `withRouting()`, ці route files мають пріоритет над `config/routing.php`. Якщо `withProviders()` явно передано, цей список має пріоритет над `config/app.php['providers']`.

У стандартному skeleton ці дублюючі виклики прибрані, тому providers/routes/middleware беруться з config.

Configuration cache і publish/cache команди наразі відсутні.
