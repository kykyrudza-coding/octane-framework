# Конфігураційні файли

Під час bootstrap framework виконує всі `config/*.php`. Назва файла стає
верхнім ключем repository:

```php
// config/app.php
return [
    'name' => env('APP_NAME', 'Octane'),
    'debug' => env('APP_DEBUG', false),
    'timezone' => 'UTC',
];
```

Читання через dot notation:

```php
$name = config('app.name');
$timezone = config('app.timezone', 'UTC');
```

Або через контракт:

```php
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;

$config = app(ConfigRepositoryContract::class);
$all = $config->all();
```

`ConfigRepository::set()` встановлює лише top-level key:

```php
$config->set('feature', ['enabled' => true]);
```

`has()` також перевіряє лише top-level key; `has('app.name')` не виконує
dot-notation lookup. Для nested value використовуйте `get()`.

Configuration cache і команди publish/cache наразі відсутні.
