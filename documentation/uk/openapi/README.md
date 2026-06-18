# OpenAPI і API docs

У framework є generated API reference для source code, але немає повноцінного OpenAPI generator.

## API reference

Docs package реєструє route і command:

```bash
php octane docs:api
```

Конфігурація:

```php
// config/docs.php
return [
    'api' => [
        'enabled' => true,
        'route' => '/_octane/api',
        'source' => null,
        'output' => APP_ROOT.'/var/framework/api-docs',
    ],
];
```

`docs.api.route` визначає HTTP route для перегляду generated HTML. `docs.api.output` використовується і командою, і controller-ом. `docs.api.source = null` означає default framework source path.

## OpenAPI

У framework і skeleton наразі немає:

- `openapi.yaml` / `openapi.json`;
- Swagger/OpenAPI annotations або PHP attributes;
- schema generator з routes/controllers;
- Swagger UI;
- middleware для request/response validation за OpenAPI.

Routing metadata містить HTTP methods, URI, action, middleware, name, prefix і group, але не містить descriptions, parameter schemas, response schemas чи security definitions. Автоматично побудувати повноцінний OpenAPI document з наявного metadata неможливо без додаткових conventions.

OpenAPI можна вести як application-owned файл, наприклад:

```text
docs/openapi.yaml
```

Його треба синхронізувати з `routes/api.php` вручну або додати окремий package/generator.
