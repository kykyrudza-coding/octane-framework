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
            return match(config('hashing.driver', 'bcrypt')) {
                'argon2' => new Argon2Hasher(),
                default  => new BcryptHasher(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
