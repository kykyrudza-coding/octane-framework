# Відомі обмеження

- `request()` helper потребує current-request binding у container.
- Route parameters не мають constraints, optional syntax, casting або model binding.
- Немає route URL generator за name.
- `HEAD` та `OPTIONS` не підтримані static routing API.
- Fallback route повертається без перевірки HTTP method.
- JSON request bodies не декодуються автоматично.
- `Request::has()` повертає `false` для keys зі значенням `null`.
- `Request::replace()` не очищає дані порожнім масивом.
- Headers з `HttpException` поки не передаються клієнту handler-ом.
- `view()` рендерить одразу; наступний `with()` є no-op.
- Prism directives використовують простий regex і погано обробляють nested parentheses.
- Prism component props підтримують лише literal string attributes.
- Prism component classes створюються без container.
- Зміна directive/component не invalidates compiled view cache.
- `vite()` має fixed `127.0.0.1:5173`.
- `Rule::exists()` потребує presence verifier; default null verifier завжди повертає `false`.
- DTO unknown field detection працює для top-level input keys.
- DTO `missing_fields = null` може повернути `null` для non-nullable constructor параметра, і тоді PHP type system все одно кине помилку під час construction.
- QueryBuilder не має pagination API.
- `database.query_log.slow_threshold` не фільтрує slow queries, а тільки документує поріг для майбутнього reporting.
- Config cache і publish commands відсутні.

Цей список треба переглядати після змін source code, оскільки документація описує фактичну реалізацію.
