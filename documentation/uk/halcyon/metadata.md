# Halcyon Metadata

Metadata описує модель без виконання database-запитів.

`MetadataParser` збирає:

- model class;
- table name з `#[Table]`;
- public typed properties;
- column mapping з `#[Column]` або snake_case convention;
- casts з `protected static function casts(): array`;
- hidden fields з `hidden()`;
- observers з `observers()`;
- scopes з `scopes()`;
- relations з protected methods, які повертають relation objects.

`MetadataRepository` відповідає за cache policy:

- коли cache disabled, metadata парситься щоразу;
- коли cache enabled, metadata читається з `MetadataCacheContract`;
- перша реалізація cache - file cache.

Конфігураційний ключ:

```php
'halcyon' => [
    'metadata' => [
        'cache' => [
            'enabled' => false,
        ],
    ],
],
```
