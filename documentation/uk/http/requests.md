# HTTP-запит

`Horizon\Http\Request\Request` створюється з PHP superglobals:

- query: `$_GET`;
- payload: `$_POST`;
- files: `$_FILES`;
- cookies: `$_COOKIE`;
- server: `$_SERVER`.

Основний API:

```php
$request->method();
$request->uri();
$request->input('name', $default);
$request->get('page', 1);
$request->post('email');
$request->file('avatar');
$request->cookie('session');
$request->server('HTTP_ACCEPT');
$request->all();
$request->allQuery();
$request->allPayload();
$request->has('email');
$request->isMethod('PATCH');
$request->isGet();
$request->isPost();
```

`input()` спочатку читає POST payload, потім query. `all()` об'єднує query і
payload так, що payload перезаписує однакові query keys. `uri()` повертає лише
path без query string.

Важливі обмеження:

- JSON body не декодується;
- `has()` використовує `isset()`, тому key зі значенням `null` вважається
  відсутнім;
- `replace()` не може очистити query/payload порожнім масивом, бо порожні
  arguments ігноруються;
- nested keys не читаються через dot notation.

У controller або closure request можна отримати через type hint `Request` чи
`RequestContract`. Global `request()` helper наразі не має гарантованого
container binding і поза action може завершитися помилкою.
