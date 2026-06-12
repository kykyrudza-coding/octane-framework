# HTTP-відповіді

## Звичайна response

```php
return response('Created', 201, [
    'X-Resource-Id' => '42',
]);
```

Без body helper повертає factory:

```php
return response()->html('<h1>Hello</h1>');
return response()->json(['ok' => true], 200);
return response()->redirect('/login');
return response()->noContent();
return response()->view('users.show', ['user' => $user]);
```

Окремий redirect helper:

```php
return redirect('/dashboard', 302);
```

`Response` за замовчуванням має `Content-Type: text/html; charset=UTF-8`.
Header names нормалізуються, наприклад `x-request-id` стає `X-Request-Id`.

Response modifiers immutable:

```php
$response = response('OK')
    ->withStatus(202)
    ->withHeader('X-Job', 'queued')
    ->withHeaders(['Cache-Control' => 'no-store']);
```

`JsonResponse` кодує data лише під час `send()`. До цього `getBody()` буде
порожнім. Помилка `json_encode()` перетворюється на `RuntimeException`.

`RedirectResponse::setTargetUrl()` повертає clone із новим `Location`.
Session flash API для redirects ще відсутній.
