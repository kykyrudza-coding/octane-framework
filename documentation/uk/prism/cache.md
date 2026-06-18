# Кеш Prism

Source view компілюється у PHP-файл:

```text
var/cache/prism/{sha1-absolute-source-path}.php
```

Шлях і ввімкнення кешу задаються в `config/prism.php`:

```php
'compiler' => [
    'cache' => [
        'enabled' => true,
        'path' => APP_ROOT.'/var/cache/prism',
    ],
],
```

Коли cache enabled, recompile виконується, якщо:

- compiled file не існує;
- `filemtime(source) > filemtime(compiled)`.

Коли `enabled` дорівнює `false`, source view компілюється під час кожного render.

Каталог кешу створюється автоматично з permissions `0755`. Окремої CLI-команди для очищення кешу наразі немає.

Ручне очищення безпечне, коли application не обробляє запити:

```powershell
Remove-Item -Recurse -Force var\cache\prism
```

Cache key залежить від абсолютного path, а не від contents. Після переміщення проєкту старі cache files можуть залишитися невикористаними. Зміна custom directive/component не invalidates compiled view cache автоматично; у такому випадку очистіть Prism cache.
