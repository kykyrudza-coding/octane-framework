# OpenAPI: поточний стан

У framework і skeleton не знайдено:

- `openapi.yaml` / `openapi.json`;
- Swagger/OpenAPI annotations або PHP attributes;
- generator-а schema з routes/controllers;
- Swagger UI;
- middleware для request/response validation за OpenAPI.

Routing metadata містить HTTP methods, URI, action, middleware, name, prefix
і group, але не містить descriptions, parameter schemas, response schemas чи
security definitions. Автоматично побудувати повноцінний OpenAPI document з
наявного metadata неможливо без додаткових conventions.

OpenAPI можна вести як application-owned файл, наприклад:

```text
docs/openapi.yaml
```

При цьому його потрібно синхронізувати з `routes/api.php` вручну або додати
окремий package/generator.
