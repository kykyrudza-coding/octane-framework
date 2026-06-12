<?php

declare(strict_types=1);

namespace Horizon\Contracts\Support\Hashing;

interface HasherContract
{
    public function hash(string $value): string;
    public function verify(string $value, string $hash): bool;
    public function needsRehash(string $hash): bool;
}
