# Шляхи застосунку

Стандартні paths:

| Binding | Метод | Значення |
|---|---|---|
| `path.base` | `basePath()` | корінь застосунку |
| `path.app` | `appPath()` | `app/` |
| `path.public` | `publicPath()` | `public/` |
| `path.var` | `varPath()` | `var/` |
| `path.config` | `configPath()` | `config/` |
| `path.db` | `dbPath()` | `db/` |
| `path.cache` | `cachePath()` | `var/cache/` |
| `path.log` | `logPath()` | `var/logs/` |
| `path.ui` | `uiPath()` | `ui/` |

Зміна paths:

```php
->withPaths(function (Application $app) {
    $app->useUiPath($app->basePath('resources'));
    $app->useVarPath($app->basePath('storage'));
})
```

Методи path API нормалізують розділювачі під поточну ОС і можуть приймати
додатковий відносний шлях:

```php
$app->uiPath('views/admin/dashboard.prism.php');
```

Provider-и Prism читають `path.ui` і `path.cache` з container. Під час
`create()` framework прив'язує paths після виконання `withPaths()` і до
початку реєстрації providers.
