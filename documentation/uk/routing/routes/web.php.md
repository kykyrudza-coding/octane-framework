# `routes/web.php`

Web route-файл підключається у `boot/app.php`:

```php
->withRouting(
    web: APP_ROOT.'/routes/web.php',
)
```

Під час його виконання registrar встановлює route group `web`. Через це до
маршрутів автоматично застосовується список `$middleware->web(...)`.

```php
<?php

use App\Http\Controllers\HomeController;
use Horizon\Routing\Route;

Route::get('/', [HomeController::class, 'index']);
Route::post('/contact', [HomeController::class, 'contact']);
```

Framework пропускає route-файл, якщо його не існує. Це не спричиняє винятку,
але маршрути з нього не будуть зареєстровані.

У skeleton web group має вбудований `ValidatePostSize`. Global
`ConvertEmptyStringsToNull` також виконується для web routes.
