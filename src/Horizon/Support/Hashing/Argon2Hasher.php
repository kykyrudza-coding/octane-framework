<?php

declare(strict_types=1);

namespace Horizon\Support\Hashing;

use Horizon\Contracts\Support\Hashing\HasherContract;
use RuntimeException;

final readonly class Argon2Hasher implements HasherContract
{
    public function __construct(
        private int $memory = 65536,
        private int $time = 4,
        private int $threads = 1,
    ) {}

    public function hash(string $value): string
    {
        $hash = password_hash($value, PASSWORD_ARGON2ID, [
            'memory_cost' => $this->memory,
            'time_cost' => $this->time,
            'threads' => $this->threads,
        ]);

        if ($hash === false) {
            throw new RuntimeException('Argon2 hashing not supported.');
        }

        return $hash;
    }

    public function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => $this->memory,
            'time_cost'   => $this->time,
            'threads'     => $this->threads,
        ]);
    }
}
