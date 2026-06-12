# Octane Framework: документація українською

Ця документація описує поточний код `octane-framework` 2.0.0 і структуру
`octane-application`, а не заплановані можливості. Приклади звірені з
публічними контрактами, реалізаціями та тестами framework.

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

## HTTP

- [Файл `routes/web.php`](routing/routes/web.php.md)
- [Файл `routes/api.php`](routing/routes/api.php.md)
- [Методи та actions](routing/methods-and-actions.md)
- [Параметри маршрутів](routing/route-parameters.md)
- [Групи маршрутів](routing/groups.md)
- [Іменовані та fallback-маршрути](routing/named-and-fallback-routes.md)
- [HTTP-запит](http/requests.md)
- [Контролери та ін'єкція аргументів](http/controllers.md)
- [HTTP-відповіді](http/responses.md)
- [Middleware](http/middleware.md)
- [Обробка винятків](http/exceptions.md)

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
- [Database: поточний стан](database/README.md)
- [CLI: поточний стан](cli/README.md)
- [OpenAPI: поточний стан](openapi/README.md)
- [Запуск тестів](testing/README.md)
- [Реалізовані можливості](reference/implemented-features.md)
- [Відсутні та незавершені можливості](reference/unavailable-features.md)
- [Відомі обмеження](reference/known-limitations.md)

> Важливо: класи або файли-заготовки без робочої реалізації не подаються як
> готовий API. Їхній стан винесено до розділу reference.
