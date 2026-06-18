<?php

declare(strict_types=1);

namespace Horizon\Validation\Providers;

use Horizon\Contracts\Validation\PresenceVerifierContract;
use Horizon\Contracts\Validation\ValidatorFactoryContract;
use Horizon\Support\Providers\ServiceProvider;
use Horizon\Validation\Presence\NullPresenceVerifier;
use Horizon\Validation\ValidatorFactory;

final class ValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PresenceVerifierContract::class,
            NullPresenceVerifier::class,
        );

        $this->app->singleton(
            ValidatorFactoryContract::class,
            fn () => new ValidatorFactory(
                presenceVerifier: $this->app->make(PresenceVerifierContract::class),
            ),
        );
    }
}
