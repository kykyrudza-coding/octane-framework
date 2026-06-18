# Service providers

Provider наслідує `Horizon\Support\Providers\ServiceProvider`:

```php
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            UserRepository::class,
            DatabaseUserRepository::class,
        );
    }

    public function boot(): void
    {
        // Робота після register() усіх providers.
    }
}
```

Application providers перелічуються в `config/app.php`:

```php
use App\Providers\AppServiceProvider;

return [
    'providers' => [
        AppServiceProvider::class,
    ],
];
```

Framework providers автоматично читаються з `src/Horizon/*/components.json`. Application providers додаються після завантаження `config/*.php`. Некоректні або неіснуючі provider classes у списку мовчки відфільтровуються.

Provider може визначити пріоритет:

```php
public static int $priority = 20;
```

Вищий priority означає раніше `register()`. Після реєстрації всіх providers framework викликає `boot()` у тому самому відсортованому порядку. Повторна реєстрація одного provider class ігнорується.

У `register()` слід додавати container bindings. У `boot()` вже можна отримувати сервіси інших providers, читати config і реєструвати Prism directives/components, Halcyon observers або console commands.

`boot/providers.php` був старим місцем реєстрації providers. У новому skeleton його немає; використовуйте тільки `config/app.php['providers']`, щоб не мати двох джерел правди.
