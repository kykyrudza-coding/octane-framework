# Параметри маршрутів

Параметр задається фігурними дужками:

```php
Route::get('/posts/{id}', function (string $id) {
    return $id;
});

Route::get('/blog/{category}/{slug}', [
    PostController::class,
    'show',
]);
```

Matcher перетворює `{name}` на named regex group `[^/]+`. Значення:

- не містить `/`;
- не URL-decode-иться окремим framework API;
- завжди передається як рядок;
- не має optional syntax або regex constraints.

Спочатку аргумент action зіставляється за ім'ям:

```php
public function show(string $slug): string
```

Як fallback framework може використати позицію route parameter. Надійніше
залишати однакові імена у URI та method signature.

Class-typed аргументи resolved через container, а `Request` /
`RequestContract` отримує поточний request:

```php
public function show(Request $request, Repository $repo, string $id): Response
```

`BindRouteParameters` у HTTP pipeline зараз нічого не робить. Model binding,
enum casting і custom parameter binders відсутні.
