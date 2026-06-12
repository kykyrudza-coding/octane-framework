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

Реєстрація:

```php
->withMiddleware(function (MiddlewareCollection $middleware) {
    $middleware->global([
        AddRequestId::class,
    ]);

    $middleware->web([
        StartSession::class,
    ]);

    $middleware->api([
        ApiAuthentication::class,
    ]);
})
```

Route middleware:

```php
Route::get('/admin', fn () => 'admin')
    ->middleware([Authenticate::class, AuthorizeAdmin::class]);
```

Порядок: global → route resolution → group → route. Middleware може
short-circuit chain, повернувши response без `$next`.

Вбудовані middleware:

- `ConvertEmptyStringsToNull` є global і рекурсивно перетворює `''` на `null`;
- `ValidatePostSize` є web і порівнює `CONTENT_LENGTH` з `post_max_size`;
- `TrimStrings` існує, але автоматично не реєструється.

Після відправлення response kernel викликає optional
`terminate($context, $response)` на всіх middleware. `ValidatePostSize`
кидає звичайний `RuntimeException`, тому завеликий request наразі дає 500, а
не 413.
