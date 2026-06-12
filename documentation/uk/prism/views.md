# Prism views і дані

Views за замовчуванням знаходяться у `ui/views`. Пошук виконується в такому
порядку:

1. `.prism.php`;
2. `.php`;
3. `.html`.

Dot notation відповідає вкладеним каталогам:

```php
return view('users.profile', [
    'user' => $user,
]);
```

```text
ui/views/users/profile.prism.php
```

`view()` повертає вже rendered `RenderedView`, який є `Stringable`.
Тому всі local data потрібно передавати одразу:

```php
return view('dashboard', ['stats' => $stats]);
```

Виклик `view('dashboard')->with(...)` у поточній реалізації не змінить
результат: `RenderedView::with()` є no-op.

Global shared data:

```php
use Horizon\Contracts\Prism\PrismContract;

$prism = app(PrismContract::class);
$prism->share('appName', config('app.name'));
```

Local data перезаписує shared key з тим самим ім'ям. Перевірити існування:

```php
$prism->exists('users.profile');
```
