# Структура застосунку

Поточний skeleton `octane-application` має таку структуру:

```text
app/
  Providers/AppServiceProvider.php
boot/
  app.php
  providers.php
config/
  app.php
  database.php
db/
  Core/
public/
  index.php
  build/
routes/
  web.php
  api.php
ui/
  css/app.css
  js/app.js
  views/welcome.prism.php
var/
  cache/prism/
```

- `app/` містить код застосунку. Skeleton наразі додає лише service provider.
- `boot/app.php` конфігурує та створює `Horizon\Arch\Application`.
- `boot/providers.php` повертає список provider-класів застосунку.
- `config/*.php` автоматично завантажуються під ключем, що дорівнює назві файла.
- `public/index.php` захоплює HTTP-запит і запускає kernel.
- `routes/` містить декларації маршрутів.
- `ui/views/` є стандартним коренем Prism views.
- `var/cache/prism/` містить скомпільовані PHP-шаблони.
- `db/` існує у skeleton, але migration runner і database layer у framework
  наразі відсутні.

Шляхи можна змінити в `withPaths()`. Bootstrap прив'яже ці значення до
container перед реєстрацією providers. Якщо змінювати path вже після
`create()`, тоді потрібно окремо викликати `bindPathsInContainer()`.
