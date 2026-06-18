# Halcyon Metadata

Metadata описує модель без виконання database-запитів.

`MetadataParser` збирає:

- model class;
- table name з `#[Table]`;
- public typed properties;
- column mapping з `#[Column]` або snake_case convention;
- casts з `protected static function casts(): array`;
- hidden fields з `hidden()`;
- observers зі static metadata;
- scopes зі static metadata;
- relations із protected methods, які повертають relation objects.

`MetadataRepository` відповідає за cache policy:

- коли cache disabled, metadata парситься щоразу;
- коли cache enabled, metadata читається з `MetadataCacheContract`;
- перша реалізація cache - file cache.

Конфігурація:

```php
return [
    'metadata' => [
        'cache' => [
            'enabled' => (bool) env('HALCYON_METADATA_CACHE', false),
            'path' => APP_ROOT.'/var/cache/halcyon',
        ],
    ],
];
```

`HalcyonServiceProvider` читає цей config під час реєстрації metadata repository.

## ORM configurator

`config/halcyon.php` також може налаштовувати ORM facade state:

```php
return [
    'relations' => [
        'morph_map' => [
            'user' => App\ORM\User::class,
        ],
    ],

    'orm' => [
        'observers' => [
            App\ORM\User::class => [
                App\Observers\UserObserver::class,
            ],
        ],

        'scopes' => [
            App\ORM\User::class => [
                App\ORM\Scopes\ActiveScope::class,
            ],
        ],

        'morph_map' => [],
    ],
];
```

Ці значення застосовуються в `HalcyonServiceProvider::boot()` до `OrmConfiguratorContract`.
