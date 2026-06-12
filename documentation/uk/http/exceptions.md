# Обробка винятків

Exception handler реєструється ще до завантаження `.env` і перехоплює
uncaught exceptions через Symfony ErrorHandler.

```php
abort(404, 'Post not found.');
abort(403, 'Forbidden', ['X-Reason' => 'policy']);
```

`abort()` кидає `HttpException`. Status береться з `getStatusCode()` або з
exception code у діапазоні 400-599.

Renderer вибирається так:

- CLI: plain text;
- `Accept: application/json`, JSON `Content-Type` або XMLHttpRequest: JSON;
- інші HTTP-запити: HTML.

`APP_DEBUG=true` додає class, message, file, line і stack trace. У production
JSON повертає узагальнені `ServerError` / `Something went wrong.`, а HTML
приховує технічні деталі.

Custom production error view шукається у:

```text
resources/views/errors/{status}.php
```

Це звичайний PHP-файл, не Prism view. Framework має fallback views для 403,
404, 500 і 503 та детальний inspector для server errors у debug mode.

Поточні обмеження:

- `HttpException::getHeaders()` handler не додає до response;
- `report()` завжди використовує `error_log()`;
- `withExceptions()` передає handler, але окремого API callback-ів для
  report/render у ньому поки немає.
