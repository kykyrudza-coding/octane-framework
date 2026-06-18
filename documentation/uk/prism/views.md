# Prism views і дані

Views за замовчуванням знаходяться в `ui/views`. Шлях і extensions задаються в `config/prism.php`:

```php
return [
    'views' => [
        'path' => APP_ROOT.'/ui/views',
        'extensions' => ['.prism.php', '.php', '.html'],
    ],
];
```

Dot notation відповідає вкладеним каталогам:

```php
return view('users.profile', [
    'user' => $user,
]);
```

```text
ui/views/users/profile.prism.php
```

`view()` повертає вже rendered `RenderedView`, який є `Stringable`. Тому local data треба передавати одразу:

```php
return view('dashboard', ['stats' => $stats]);
```

Виклик `view('dashboard')->with(...)` у поточній реалізації не змінить результат: `RenderedView::with()` є no-op.

Global shared data:

```php
use Horizon\Contracts\Prism\PrismContract;

$prism = app(PrismContract::class);
$prism->share('appName', config('app.name'));
```

Local data перезаписує shared key з тим самим іменем. Перевірити існування:

```php
$prism->exists('users.profile');
```

Компоненти й directives можна реєструвати через `config/prism.php`:

```php
'components' => [
    'aliases' => [
        'Button' => App\View\Components\Button::class,
    ],
],

'directives' => [
    'money' => App\View\Directives\MoneyDirective::class,
],
```
