# Обробка винятків

Exception handler реєструється на ранній bootstrap-фазі й перехоплює uncaught exceptions через Symfony ErrorHandler.

```php
abort(404, 'Post not found.');
abort(403, 'Forbidden', ['X-Reason' => 'policy']);
```

`abort()` кидає `HttpException`. Status береться з `getStatusCode()` або з exception code у діапазоні 400-599.

Після завантаження config handler читає `config/exceptions.php`:

```php
return [
    'debug' => (bool) env('APP_DEBUG', false),

    'reporting' => [
        'ignore' => [
            // DomainException::class,
        ],
    ],

    'rendering' => [
        'default' => 'auto', // auto, html, json, console
        'json' => [
            'pretty' => false,
        ],
        'views' => [
            'path' => null,
        ],
    ],
];
```

Renderer у режимі `auto` вибирається так:

- CLI: plain text;
- `Accept: application/json`, JSON `Content-Type` або XMLHttpRequest: JSON;
- інші HTTP-запити: HTML.

`APP_DEBUG=true` додає class, message, file, line і stack trace. У production JSON повертає узагальнені `ServerError` / `Something went wrong.`, а HTML приховує технічні деталі.

Custom production error view шукається в `exceptions.rendering.views.path`, якщо шлях задано. Інакше використовується:

```text
resources/views/errors/{status}.php
```

Це звичайний PHP-файл, не Prism view. Framework має fallback views для 403, 404, 500 і 503 та детальний inspector для server errors у debug mode.

Поточні обмеження:

- headers з `HttpException::getHeaders()` handler ще не додає до response;
- `report()` використовує `error_log()`;
- окремого API для report/render callbacks поки немає.
