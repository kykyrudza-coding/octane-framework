# Життєвий цикл HTTP-запиту

`public/index.php` виконує:

```php
$app = require APP_ROOT.'/boot/app.php';

$app->handleRequest(
    RequestContext::capture()
)->run();
```

`Application::run()` отримує `HttpKernelContract`, викликає `handle()`,
відправляє response і запускає termination.

Порядок HTTP pipeline:

1. `RunGlobalMiddleware`;
2. `ResolveRoute`;
3. `BindRouteParameters` (поки no-op);
4. `RunGroupMiddleware`;
5. `RunRouteMiddleware`;
6. `InvokeController`.

Після `$response->send()` kernel:

1. збирає global, group і route middleware;
2. викликає `terminate($context, $response)` на тих middleware, де метод існує;
3. запускає callbacks, зареєстровані через `$app->terminating(...)`.

Якщо маршрут не знайдений, `ResolveRoute` викликає
`abort(404, 'Route not found.')`. Exception handler формує HTML, JSON або
console output залежно від SAPI та request headers.
