# Bootstrap застосунку

`Application::configure($basePath)->create()` запускає bootstrap pipeline:

1. прив'язує стандартні шляхи;
2. реєструє `ApplicationContract`, `ContainerContract` і HTTP kernel;
3. реєструє exception handler;
4. завантажує `.env` і environment-specific файл;
5. завантажує `config/*.php`;
6. створює aliases `app`, `container`, `config`;
7. знаходить framework providers і додає application providers з `config/app.php`;
8. викликає `register()`, а потім `boot()` providers;
9. завантажує route files з `config/routing.php`;
10. застосовує middleware й exception callbacks, якщо вони явно задані в builder.

Типовий `boot/app.php` після `0.2.0`:

```php
<?php

declare(strict_types=1);

use Horizon\Arch\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withEnvironment(function (Application $app) {
        $app->environmentFile(APP_ROOT.'/.env')
            ->developmentEnvironmentFile(APP_ROOT.'/.env.development')
            ->localEnvironmentFile(APP_ROOT.'/.env.local')
            ->productionEnvironmentFile(APP_ROOT.'/.env.production')
            ->testingEnvironmentFile(APP_ROOT.'/.env.testing');
    })
    ->create();
```

`boot/app.php` не повинен дублювати `providers`, `routes`, `middleware` і `exceptions`, якщо застосунок використовує стандартні config-файли. Ці значення мають жити в:

- `config/app.php`;
- `config/routing.php`;
- `config/http.php`;
- `config/exceptions.php`.

`withEnvironment()` і `withPaths()` виконуються одразу під час побудови fluent chain, бо env/config ще не завантажені. Routing і middleware застосовуються наприкінці `create()`, після реєстрації providers.

## Коли використовувати builder callbacks

Builder callbacks лишаються escape hatch:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: APP_ROOT.'/custom/web.php')
    ->withMiddleware(function ($middleware) {
        $middleware->global([App\Http\Middleware\RequestId::class]);
    })
    ->create();
```

Якщо callback дублює config, callback має пріоритет. Для звичайного skeleton краще тримати runtime defaults у `config/*.php`.
