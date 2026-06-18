<?php

declare(strict_types=1);

namespace Horizon\Support\Providers;

use Horizon\Contracts\Support\Hashing\HasherContract;
use Horizon\Support\Hashing\Argon2Hasher;
use Horizon\Support\Hashing\BcryptHasher;

class SupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HasherContract::class, function () {
            return match (config('hashing.driver', 'bcrypt')) {
                'argon2' => new Argon2Hasher(
                    memory: (int) config('hashing.argon2.memory', 65536),
                    time: (int) config('hashing.argon2.time', 4),
                    threads: (int) config('hashing.argon2.threads', 1),
                ),
                default => new BcryptHasher(
                    rounds: (int) config('hashing.bcrypt.rounds', 10),
                ),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
