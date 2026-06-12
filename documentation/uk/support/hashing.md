# Хешування

Контракт:

```php
use Horizon\Contracts\Support\Hashing\HasherContract;

$hasher = app(HasherContract::class);
$hash = $hasher->hash($password);

if ($hasher->verify($password, $hash)) {
    // valid
}

if ($hasher->needsRehash($hash)) {
    $hash = $hasher->hash($password);
}
```

`SupportServiceProvider` читає `config('hashing.driver', 'bcrypt')`:

- `bcrypt`: `BcryptHasher`, default cost 10;
- `argon2`: `Argon2Hasher` на базі Argon2id, default memory 65536, time 4,
  threads 1.

Приклад configuration:

```php
// config/hashing.php
return [
    'driver' => env('HASH_DRIVER', 'bcrypt'),
];
```

Параметри cost/memory не зчитуються з config автоматично. Для custom
параметрів перевизначте binding у provider, який реєструється після
framework provider:

```php
public static int $priority = -10;

$this->app->singleton(
    HasherContract::class,
    fn () => new BcryptHasher(rounds: 12),
);
```

Static `Horizon\Support\Hashing\Hasher` має окремий internal driver і не
використовує container binding, доки явно не викликано `Hasher::setDriver()`.
