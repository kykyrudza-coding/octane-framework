# `routes/api.php`

API route file завантажується з `config/routing.php`:

```php
return [
    'files' => [
        'api' => APP_ROOT.'/routes/api.php',
    ],

    'groups' => [
        'api' => [
            'prefix' => 'api',
            'name' => 'api.',
        ],
    ],
];
```

Груповий prefix `api` означає, що маршрут `/users` у `routes/api.php` стане `/api/users`:

```php
<?php

use App\Controllers\UserController;
use Horizon\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
```

API middleware береться з `config/http.php['middleware']['api']`.

Автоматичні JSON responses, rate limiting, authentication чи OpenAPI validation не додаються самі. Їх треба підключати middleware або controller code.
