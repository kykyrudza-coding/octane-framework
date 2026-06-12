# Bootstrap застосунку

`Application::configure($basePath)->create()` запускає bootstrap pipeline:

1. прив'язує стандартні шляхи;
2. реєструє `ApplicationContract`, `ContainerContract` і HTTP kernel;
3. реєструє та запускає exception handler;
4. завантажує `.env` і environment-specific файл;
5. завантажує `config/*.php`;
6. створює aliases `app`, `container`, `config`;
7. знаходить framework providers і додає providers застосунку;
8. викликає `register()`, а потім `boot()` providers;
9. завантажує route-файли та виконує middleware/exception callbacks.

Типовий `boot/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withPaths(function (Application $app) {
        // $app->useUiPath(...);
    })
    ->withProviders(APP_ROOT.'/boot/providers.php')
    ->withRouting(
        web: APP_ROOT.'/routes/web.php',
        api: APP_ROOT.'/routes/api.php',
    )
    ->withMiddleware(function (MiddlewareCollection $middleware) {
        // $middleware->global([...]);
    })
    ->withExceptions(function ($handler) {
        // Поточний Handler не має API для register/render callbacks.
    })
    ->withEnvironment(function (Application $app) {
        $app->environmentFile(APP_ROOT.'/.env');
    })
    ->create();
```

`withEnvironment()` і `withPaths()` виконують callback одразу під час
побудови fluent chain. Натомість routing і middleware застосовуються наприкінці
`create()`.
