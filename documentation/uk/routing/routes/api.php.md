# `routes/api.php`

Щоб API routes реально завантажувались, передайте файл окремим аргументом:

```php
->withRouting(
    web: APP_ROOT.'/routes/web.php',
    api: APP_ROOT.'/routes/api.php',
)
```

Поточний skeleton має `routes/api.php`, але у `boot/app.php` передає лише
`web`. Отже API-файл за замовчуванням не виконується.

Приклад:

```php
<?php

use Horizon\Routing\Route;

Route::get('/api/health', function () {
    return response()->json(['status' => 'ok']);
});
```

Framework не додає `/api` автоматично. Prefix треба записати у URI або
створити group:

```php
Route::prefix('/api')->group(function ($routes) {
    $routes->get('/users', [UserController::class, 'index']);
});
```

API group визначає тільки вибір `$middleware->api(...)`. Автоматичні JSON
responses, rate limiting, authentication чи OpenAPI generation відсутні.
