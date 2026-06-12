# Тестування framework

Встановлення dev dependencies:

```bash
composer install
```

Запуск:

```bash
composer test
composer phpstan
composer pint
composer check
```

`composer check` послідовно запускає PHPStan, PHPUnit і Pint у test mode.
У поточному source tree цей quality gate не проходить: PHPStan повідомляє
113 errors, а Pint знаходить style differences у framework і tests.

На момент підготовки документації PHPUnit suite містить 114 tests і перевіряє:

- application/container/pipeline;
- environment/configuration;
- routing;
- HTTP responses, request context та controller injection;
- exception renderers;
- Prism compiler, views, layouts і components.

Suite проходить, але PHPUnit повідомляє 13 notices. Support utilities,
helpers, middleware integration, full HTTP kernel bootstrap, CLI і database
layer не мають еквівалентного test coverage.
