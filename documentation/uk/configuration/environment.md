# Змінні середовища

Спочатку завантажується базовий `.env` через immutable Dotenv. `APP_ENV`
визначає другий файл:

| `APP_ENV` | Файл |
|---|---|
| `development` | `.env.development` |
| `testing` | `.env.testing` |
| `local` | `.env.local` |
| будь-яке інше значення | `.env.production` |

Другий файл завантажується лише якщо існує і може перевизначити значення
базового файла.

```php
->withEnvironment(function (Application $app) {
    $app->environmentFile(APP_ROOT.'/.env')
        ->developmentEnvironmentFile(APP_ROOT.'/.env.development')
        ->testingEnvironmentFile(APP_ROOT.'/.env.testing')
        ->localEnvironmentFile(APP_ROOT.'/.env.local')
        ->productionEnvironmentFile(APP_ROOT.'/.env.production');
})
```

Helper `env()` читає `$_ENV`, потім `$_SERVER`, потім `getenv()`. Він
перетворює `true`, `false`, `empty`, `null` та варіанти в дужках на відповідні
PHP values. Інші значення повертаються рядками.

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
