# Методи та actions

Публічний static API `Horizon\Routing\Route` підтримує:

```php
Route::get($uri, $action);
Route::post($uri, $action);
Route::put($uri, $action);
Route::patch($uri, $action);
Route::delete($uri, $action);
```

Підтримувані actions:

```php
Route::get('/closure', fn () => 'OK');

Route::get('/array', [
    UserController::class,
    'index',
]);

Route::get('/string', UserController::class.'@index');
```

String action без `@` є некоректним. Controller class створюється через
container.

Немає окремих helpers для `HEAD`, `OPTIONS`, multi-method routes або resource
routes. Нижчий рівень `RouteRegistrar::createPendingRoute()` приймає масив
методів, але static facade не відкриває цей сценарій.

Route реєструється, коли `PendingRoute` знищується, або явно:

```php
$pending = Route::get('/health', fn () => 'ok');
$route = $pending->register();
```

Повторний `register()` кидає `LogicException`.
