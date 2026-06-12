# Групи маршрутів

Group attributes накопичуються:

```php
Route::prefix('/admin')
    ->name('admin.')
    ->middleware([Authenticate::class])
    ->group(function ($routes) {
        $routes->get('/users', [UserController::class, 'index'])
            ->name('users.index');
    });
```

Результат:

- URI: `/admin/users`;
- name: `admin.users.index`;
- middleware: `[Authenticate::class]`.

Nested prefixes також накопичуються:

```php
Route::prefix('/api')->group(function ($routes) {
    $routes->prefix('/v1')->group(function ($routes) {
        $routes->get('/users', fn () => 'users');
    });
});
```

Після callback попередній group state відновлюється. Middleware arrays
об'єднуються без автоматичного видалення дублікатів.

`web` і `api` є внутрішніми route groups, які встановлюються під час
завантаження відповідних route-файлів. Вони не додають URI prefix.
