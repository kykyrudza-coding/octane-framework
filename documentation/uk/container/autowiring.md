# Autowiring

Якщо explicit binding відсутній, container намагається створити class через
reflection і рекурсивно resolved constructor dependencies:

```php
final class ReportService
{
    public function __construct(
        private ReportRepository $repository,
    ) {}
}

$service = app(ReportService::class);
```

Інтерфейси потрібно прив'язувати:

```php
app()->bind(
    ReportRepositoryContract::class,
    DatabaseReportRepository::class,
);
```

Правила для constructor parameters:

- class type створюється або береться з binding;
- primitive type використовує default value, якщо він є;
- nullable parameter без binding отримує `null`;
- untyped parameter без default спричиняє `RuntimeException`;
- union/intersection type без default не підтримується;
- circular dependency виявляється і завершується винятком із chain класів.

Factory callback отримує сам `Container`:

```php
app()->singleton(Service::class, function ($container) {
    return new Service($container->make(Client::class));
});
```
