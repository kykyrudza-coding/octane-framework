<?php

declare(strict_types=1);

namespace Horizon\Console\Providers;

use Horizon\Console\CommandRegistry;
use Horizon\Console\Commands\ListCommand;
use Horizon\Console\Commands\VersionCommand;
use Horizon\Console\ConsoleKernel;
use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Contracts\Console\ConsoleKernelContract;
use Horizon\Support\Providers\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ConsoleKernelContract::class,
            ConsoleKernel::class
        );

        $this->app->singleton(
            CommandRegistryContract::class,
            CommandRegistry::class
        );
    }

    public function boot(): void
    {
        $commands = $this->app->make(CommandRegistryContract::class);

        $commands->register(ListCommand::class);
        $commands->register(VersionCommand::class);
    }
}
