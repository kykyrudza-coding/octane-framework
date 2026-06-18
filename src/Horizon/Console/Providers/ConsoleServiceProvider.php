<?php

declare(strict_types=1);

namespace Horizon\Console\Providers;

use Horizon\Console\CommandRegistry;
use Horizon\Console\Commands\AboutCommand;
use Horizon\Console\Commands\ListCommand;
use Horizon\Console\Commands\VersionCommand;
use Horizon\Console\ConsoleKernel;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Console\CommandContract;
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

        $commands->register(AboutCommand::class);
        $commands->register(ListCommand::class);
        $commands->register(VersionCommand::class);

        foreach ($this->configuredCommands() as $command) {
            $commands->register($command);
        }
    }

    /**
     * @return list<class-string<CommandContract>>
     */
    private function configuredCommands(): array
    {
        $config = $this->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return [];
        }

        $commands = $config->get('console.commands', []);

        if (! is_array($commands)) {
            return [];
        }

        return array_values(array_filter(
            $commands,
            static fn (mixed $command): bool => is_string($command) && is_subclass_of($command, CommandContract::class),
        ));
    }
}
