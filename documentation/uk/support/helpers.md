# Глобальні helpers

Framework autoload-ить `src/Horizon/Arch/helpers.php`.

```php
app();                         // Application
app(Service::class);          // resolve binding
container();                   // ContainerContract
config('app.name', 'Octane');
env('APP_DEBUG', false);
request();                     // RequestContract, див. застереження нижче
response('OK', 200);
response()->json(['ok' => true]);
redirect('/login');
abort(404, 'Not found');
view('welcome', ['name' => 'Octane']);
vite('ui/js/app.js');
```

Застереження:

- `request()` викликає `app(RequestContract::class)`, але framework не
  реєструє current request у container. У controller використовуйте type hint;
- `env()` призначений для configuration files. Після bootstrap application
  code краще читає `config()`;
- `abort()` лише кидає exception і не повертає response;
- `vite()` перевіряє fixed endpoint `127.0.0.1:5173`;
- `view()` рендерить одразу.
