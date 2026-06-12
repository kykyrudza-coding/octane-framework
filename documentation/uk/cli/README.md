# CLI: поточний стан

Framework не містить namespace `Horizon\Console`, command registry або console
kernel.

Файл `octane` у skeleton намагається створити:

```php
$app = new Kernel\Console\App();
```

Такого class немає ні в `octane-framework`, ні в `octane-application`.
Отже команди:

```bash
php octane
php octane list
php octane serve
```

у поточному стані не є підтримуваним API.

Для локального HTTP server використовуйте:

```bash
php -S 127.0.0.1:8000 -t public
```

Для власних scripts можна створити окремий PHP entry point, підключити
`vendor/autoload.php` та `boot/app.php`, але це буде application-specific
рішення, не вбудована command system.
