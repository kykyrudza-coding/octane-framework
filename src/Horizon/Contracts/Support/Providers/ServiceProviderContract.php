<?php

declare(strict_types=1);

namespace Horizon\Contracts\Support\Providers;

interface ServiceProviderContract
{
    public function register(): void;

    public function boot(): void;
}
