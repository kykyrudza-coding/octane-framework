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

`SupportServiceProvider` читає `config/hashing.php`:

```php
return [
    'driver' => env('HASH_DRIVER', 'bcrypt'),

    'bcrypt' => [
        'rounds' => (int) env('BCRYPT_ROUNDS', 10),
    ],

    'argon2' => [
        'memory' => (int) env('ARGON2_MEMORY', 65536),
        'time' => (int) env('ARGON2_TIME', 4),
        'threads' => (int) env('ARGON2_THREADS', 1),
    ],
];
```

Підтримані drivers:

- `bcrypt`: `BcryptHasher`;
- `argon2`: `Argon2Hasher` на базі Argon2id.

Static `Horizon\Support\Hashing\Hasher` має окремий internal driver і не використовує container binding, доки явно не викликано `Hasher::setDriver()`.
