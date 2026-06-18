<?php

declare(strict_types=1);

namespace Horizon\Support\Providers;

use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;

abstract class ServiceProvider implements ServiceProviderContract
{
    /**
     * Registration priority. Higher = registers first.
     * Core framework providers use 100, user providers default to 0.
     */
    public static int $priority = 0;

    public function __construct(
        protected ApplicationContract $app,
    ) {}

    public function register(): void {}

    public function boot(): void {}
}
