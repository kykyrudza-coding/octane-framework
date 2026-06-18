# Middleware

Middleware реалізує `MiddlewareContract`:

```php
final class Authenticate implements MiddlewareContract
{
    public function handle(
        RequestContextContract $context,
        Closure $next,
    ): ResponseContract {
        if (! $this->isAuthenticated($context->getRequest())) {
            return redirect('/login');
        }

        return $next($context);
    }
}
```

## Реєстрація

Стандартна реєстрація middleware відбувається через `config/http.php`:

```php
return [
    'requests' => [
        'trim_strings' => true,
        'convert_empty_strings_to_null' => true,
        'max_post_size_validation' => true,
    ],

    'middleware' => [
        'global' => [
            App\Http\Middleware\RequestId::class,
        ],

        'web' => [
            App\Http\Middleware\StartSession::class,
        ],

        'api' => [
            App\Http\Middleware\ApiAuthentication::class,
        ],
    ],
];
```

`RouteServiceProvider` читає цей конфіг під час `boot()` і додає middleware в `MiddlewareCollection`.

Вбудовані toggles:

- `trim_strings`: додає `TrimStrings` у global middleware;
- `convert_empty_strings_to_null`: додає `ConvertEmptyStringsToNull` у global middleware;
- `max_post_size_validation`: додає `ValidatePostSize` у web middleware.

Route middleware задається прямо на маршруті:

```php
Route::get('/admin', fn () => 'admin')
    ->middleware([Authenticate::class, AuthorizeAdmin::class]);
```

Порядок виконання: global -> route resolution -> group -> route. Middleware може short-circuit chain, повернувши response без `$next`.

Після відправлення response kernel викликає optional `terminate($context, $response)` на всіх middleware, які мають такий метод.

## Builder override

`boot/app.php` усе ще може викликати `withMiddleware()`, але це ручний override:

```php
->withMiddleware(function (MiddlewareCollection $middleware) {
    $middleware->global([RequestId::class]);
})
```

Для стандартного skeleton використовуйте `config/http.php`, щоб middleware не дублювався між bootstrap і config.
