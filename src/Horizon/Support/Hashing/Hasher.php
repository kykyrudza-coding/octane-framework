<?php

declare(strict_types=1);

namespace Horizon\Support\Hashing;

use Horizon\Contracts\Support\Hashing\HasherContract;

final class Hasher
{
    private static ?HasherContract $driver = null;

    public static function setDriver(HasherContract $driver): void
    {
        Hasher::$driver = $driver;
    }

    public static function hash(string $value): string
    {
        return Hasher::driver()->hash($value);
    }

    public static function verify(string $value, string $hash): bool
    {
        return Hasher::driver()->verify($value, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return Hasher::driver()->needsRehash($hash);
    }

    private static function driver(): HasherContract
    {
        if (Hasher::$driver === null) {
            Hasher::$driver = new BcryptHasher();
        }

        return Hasher::$driver;
    }
}
