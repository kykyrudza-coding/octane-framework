# Відомі обмеження

- Skeleton не підключає `routes/api.php` у `boot/app.php`.
- `request()` helper не має current-request binding у container.
- Route parameters не мають constraints, optional syntax, casting або model
  binding.
- Немає route URL generator за name.
- `HEAD` та `OPTIONS` не підтримані static routing API.
- Fallback route повертається без перевірки HTTP method.
- JSON request bodies не декодуються.
- `Request::has()` повертає `false` для keys зі значенням `null`.
- `Request::replace()` не очищає дані порожнім масивом.
- `ValidatePostSize` повертає 500 замість 413.
- Headers з `HttpException` не передаються клієнту handler-ом.
- `view()` рендерить одразу; наступний `with()` є no-op.
- Prism directives використовують простий regex і погано обробляють nested
  parentheses.
- Callable custom directives конфліктують із return type registry; використовуйте
  `DirectiveContract`.
- Prism component props підтримують лише literal string attributes.
- Prism component classes створюються без container.
- Зміна directive/component не invalidates compiled view cache.
- `vite()` має fixed `127.0.0.1:5173`.
- `BenchmarkResult::average()` має runtime defect.
- CLI entry point і database skeleton не працездатні.

Цей список слід переглядати після змін source code, оскільки документація
навмисно описує саме фактичну реалізацію.
