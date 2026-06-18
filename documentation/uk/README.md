# Octane Framework: документація українською

Ця документація описує поточний код `octane-framework` і skeleton `octane-application`, а не заплановані можливості. Приклади звірені з публічними контрактами, реалізаціями та тестами framework.

## Початок роботи

- [Вимоги](getting-started/requirements.md)
- [Встановлення](getting-started/installation.md)
- [Структура застосунку](getting-started/application-structure.md)
- [Перший маршрут](getting-started/first-application.md)
- [Vite і frontend-ресурси](getting-started/frontend-assets.md)

## Ядро

- [Огляд архітектури](architecture/overview.md)
- [Bootstrap застосунку](architecture/bootstrap.md)
- [Життєвий цикл HTTP-запиту](architecture/request-lifecycle.md)
- [Шляхи застосунку](architecture/paths.md)
- [Pipeline](architecture/pipeline.md)
- [Контейнер залежностей](container/bindings.md)
- [Autowiring](container/autowiring.md)
- [Service providers](container/service-providers.md)
- [Конфігураційні файли](configuration/configuration-files.md)
- [Змінні середовища](configuration/environment.md)

## HTTP і Routing

- [Файл `routes/web.php`](routing/routes/web.php.md)
- [Файл `routes/api.php`](routing/routes/api.php.md)
- [Методи та actions](routing/methods-and-actions.md)
- [Параметри маршрутів](routing/route-parameters.md)
- [Групи маршрутів](routing/groups.md)
- [Іменовані та fallback-маршрути](routing/named-and-fallback-routes.md)
- [HTTP-запит](http/requests.md)
- [Контролери та інʼєкція аргументів](http/controllers.md)
- [HTTP-відповіді](http/responses.md)
- [Middleware](http/middleware.md)
- [Обробка винятків](http/exceptions.md)

## Data і Validation

- [DTO](dto/README.md)
- [Validation](validation/README.md)

## Database, QueryBuilder і Halcyon

- [Database](database/README.md)
- [QueryBuilder](query-builder/README.md)
- [Halcyon ORM](halcyon/README.md)
- [Halcyon metadata](halcyon/metadata.md)
- [Halcyon hydration](halcyon/hydration.md)
- [Halcyon relations](halcyon/relations.md)
- [Halcyon та QueryBuilder](halcyon/query-builder-integration.md)

## Prism

- [Views і дані](prism/views.md)
- [Виведення та директиви](prism/echo-and-directives.md)
- [Layouts, blocks та imports](prism/layouts-and-imports.md)
- [Компоненти](prism/components.md)
- [Кеш шаблонів](prism/cache.md)

## Інше

- [Глобальні helpers](support/helpers.md)
- [Хешування](support/hashing.md)
- [Допоміжні класи](support/utilities.md)
- [CLI](cli/README.md)
- [OpenAPI](openapi/README.md)
- [Запуск тестів](testing/README.md)
- [Release 0.2.0](releases/0.2.0.md)
- [Реалізовані можливості](reference/implemented-features.md)
- [Відсутні та незавершені можливості](reference/unavailable-features.md)
- [Відомі обмеження](reference/known-limitations.md)
