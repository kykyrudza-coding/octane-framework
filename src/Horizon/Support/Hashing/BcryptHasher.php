<?php

declare(strict_types=1);

namespace Horizon\Support\Hashing;

use Horizon\Contracts\Support\Hashing\HasherContract;
use Horizon\Support\Exceptions\HashingException;

final readonly class BcryptHasher implements HasherContract
{
    public function __construct(
        private int $rounds = 10,
    ) {}

    public function hash(string $value): string
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->rounds
        ]);

        if ($hash === false) {
            throw new HashingException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    public function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, [
            'cost' => $this->rounds
        ]);
    }
}
