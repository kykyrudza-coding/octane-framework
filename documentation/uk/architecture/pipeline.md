# Pipeline

`Horizon\Arch\Pipeline\Pipeline` передає payload через послідовність об'єктів
із callable-методом `handle($payload, Closure $next)`.

```php
$result = (new Pipeline(container()))
    ->send($payload)
    ->through([
        NormalizeInput::class,
        AuthorizeOperation::class,
    ])
    ->then(fn ($payload) => execute($payload));
```

String class names резолвляться через container, тому constructor injection
працює і в pipes. Порядок виконання відповідає порядку в масиві.

```php
final class NormalizeInput implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): mixed
    {
        $payload->name = trim($payload->name);

        return $next($payload);
    }
}
```

Pipe може зупинити chain, якщо не викличе `$next`. Destination може бути
callable або class name, що резолвиться до `PipeInterface`.

Той самий механізм використовується bootstrap-ом, HTTP kernel і вкладеними
middleware chains.
