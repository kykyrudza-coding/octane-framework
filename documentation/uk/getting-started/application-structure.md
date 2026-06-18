# Структура застосунку

Поточний skeleton `octane-application` має таку структуру:

```text
app/
  Controllers/
  ORM/
  Providers/AppServiceProvider.php
boot/
  app.php
config/
  app.php
  console.php
  database.php
  docs.php
  dto.php
  exceptions.php
  halcyon.php
  hashing.php
  http.php
  prism.php
  query-builder.php
  routing.php
  validation.php
db/
  migrations/
public/
  index.php
routes/
  web.php
  api.php
ui/
  css/app.css
  js/app.js
  views/welcome.prism.php
var/
  cache/
  framework/
  logs/
  sessions/
  uploads/
```

- `app/` містить код застосунку: controllers, models, providers, requests, DTO.
- `boot/app.php` створює `Horizon\Arch\Application` і задає env file locations.
- `config/*.php` є основним місцем для runtime configuration.
- `config/app.php['providers']` містить application providers.
- `config/routing.php` визначає `routes/web.php` і `routes/api.php`.
- `config/http.php` визначає middleware groups і defaults для HTTP шару.
- `public/index.php` захоплює HTTP-запит і запускає kernel.
- `ui/views/` є стандартним коренем Prism views.
- `var/cache/` містить runtime cache, зокрема Prism і Halcyon metadata cache.

`boot/providers.php` більше не використовується в стандартному skeleton. Якщо він лишився після оновлення, перенесіть його список класів у `config/app.php['providers']`.

Шляхи можна змінювати через `withPaths()` у `boot/app.php`. Це треба робити до `create()`, бо providers отримують path bindings уже під час bootstrap.
