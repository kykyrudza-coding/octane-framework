# `routes/web.php`

Web route file завантажується з `config/routing.php`:

```php
return [
    'files' => [
        'web' => APP_ROOT.'/routes/web.php',
    ],

    'groups' => [
        'web' => [
            'prefix' => '',
            'name' => '',
        ],
    ],
];
```

Під час виконання цього файлу registrar встановлює route group `web`. Через це до маршрутів застосовується список middleware з `config/http.php['middleware']['web']` і built-in web middleware, увімкнений у `http.requests`.

```php
<?php

use App\Controllers\HomeController;
use Horizon\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::post('/contact', [HomeController::class, 'contact']);
```

Якщо route file не існує, framework пропускає його без винятку.

`boot/app.php` може явно передати `withRouting(web: ...)`; у такому разі цей шлях має пріоритет над `config/routing.php`. У стандартному skeleton маршрутні файли задаються тільки в config.
