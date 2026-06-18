# Змінні середовища

Спочатку завантажується базовий `.env` через immutable Dotenv. `APP_ENV` визначає другий файл:

| `APP_ENV` | Файл |
| --- | --- |
| `development` | `.env.development` |
| `testing` | `.env.testing` |
| `local` | `.env.local` |
| будь-яке інше значення | `.env.production` |

Другий файл завантажується лише якщо існує і може перевизначити значення базового файлу.

Файли середовища задаються в `boot/app.php`, бо env треба завантажити до `config/*.php`:

```php
->withEnvironment(function (Application $app) {
    $app->environmentFile(APP_ROOT.'/.env')
        ->developmentEnvironmentFile(APP_ROOT.'/.env.development')
        ->testingEnvironmentFile(APP_ROOT.'/.env.testing')
        ->localEnvironmentFile(APP_ROOT.'/.env.local')
        ->productionEnvironmentFile(APP_ROOT.'/.env.production');
})
```

Helper `env()` читає `$_ENV`, потім `$_SERVER`, потім `getenv()`. Він перетворює `true`, `false`, `empty`, `null` та варіанти в дужках на відповідні PHP values. Інші значення повертаються рядками.

```php
$debug = env('APP_DEBUG', false);
```

Стан application:

```php
app()->getEnvironment();
app()->isProduction();
app()->isDevelop();
app()->isTesting();
app()->isLocal();
```

## Базові ключі

```dotenv
APP_NAME=Octane
APP_ENV=development
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=UTC
APP_KEY=

SERVER_HOST=127.0.0.1
SERVER_PORT=8000
```

## Module keys

Skeleton `.env.example.development` містить приклади для активних config-файлів:

- `DB_*` для `config/database.php`;
- `QUERY_LOG` для `config/query-builder.php`;
- `HALCYON_METADATA_CACHE` для `config/halcyon.php`;
- `DTO_*` для `config/dto.php`;
- `VALIDATION_*` для `config/validation.php`;
- `HASH_DRIVER`, `BCRYPT_*`, `ARGON2_*` для `config/hashing.php`;
- `HTTP_*` для `config/http.php`;
- `PRISM_CACHE` для `config/prism.php`;
- `DOCS_API_*` для `config/docs.php`;
- `EXCEPTION_*` для `config/exceptions.php`.

Не додавайте env key, якщо його не читає жоден config-файл або provider. Інакше skeleton почне виглядати як такий, що підтримує поведінку, якої в runtime немає.
