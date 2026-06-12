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

Providers застосунку перелічуються у `boot/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
];
```

Framework providers автоматично читаються з `src/Horizon/*/components.json`.
Некоректні або неіснуючі класи у списку application providers мовчки
відфільтровуються.

Provider може визначити пріоритет:

```php
public static int $priority = 20;
```

Вищий priority означає раніше `register()`. Після реєстрації всіх providers
framework викликає `boot()` у тому самому відсортованому порядку. Повторна
реєстрація одного provider class ігнорується.

У `register()` слід додавати container bindings. У `boot()` вже можна
отримувати сервіси інших providers і реєструвати Prism directives/components.
