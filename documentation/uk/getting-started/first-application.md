# Перший маршрут

Стандартний `routes/web.php`:

```php
<?php

use Horizon\Routing\Route;

Route::get('/', function () {
    return view('welcome');
});
```

View шукається у `ui/views/welcome.prism.php`. Для простого тексту:

```php
Route::get('/hello', fn () => 'Hello, Octane!');
```

Для JSON:

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => app()->version(),
    ]);
});
```

Для параметра URL:

```php
Route::get('/users/{id}', function (string $id) {
    return response()->json(['id' => $id]);
});
```

Параметр `id` надходить як рядок. Автоматичного перетворення на `int`, model
binding або валідації framework поки не виконує.
