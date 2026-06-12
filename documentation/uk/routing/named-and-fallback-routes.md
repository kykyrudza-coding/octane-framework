# Іменовані та fallback-маршрути

Route name:

```php
Route::get('/profile', [ProfileController::class, 'show'])
    ->name('profile.show');
```

Group name prefix:

```php
Route::prefix('/admin')->name('admin.')->group(function ($routes) {
    $routes->get('/users', fn () => 'users')
        ->name('users.index');
});
```

`RouteCollectionContract::getByName()` може знайти DTO за повним ім'ям, але
global helper для генерації URL за name наразі відсутній.

Static class `Route` не має окремих methods `name()`, `middleware()` або
`group()`. Вони доступні на registrar, який повертає `Route::prefix()`.

Fallback:

```php
Route::fallback(function () {
    return response('Not found', 404);
});
```

Fallback DTO декларує `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, але collection
повертає fallback без повторної перевірки HTTP method. Фактично він може
спрацювати і для іншого method, якщо звичайного match немає. Framework не
встановлює 404 автоматично для fallback, тому action має повернути потрібний
status самостійно.

Без fallback невідомий URI завершується `HttpException` зі status 404.
