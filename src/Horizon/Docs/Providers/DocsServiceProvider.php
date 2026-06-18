<?php

declare(strict_types=1);

namespace Horizon\Docs\Providers;

use Horizon\Contracts\Console\CommandRegistryContract;
use Horizon\Contracts\Routing\RouteRegistrarContract;
use Horizon\Docs\Console\ApiDocsGenerateCommand;
use Horizon\Docs\Controllers\ApiDocsController;
use Horizon\Docs\Exceptions\DocsBindingException;
use Horizon\Support\Providers\ServiceProvider;

final class DocsServiceProvider extends ServiceProvider
{
    public static int $priority = -100;

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerCommands();
    }

    private function registerRoutes(): void
    {
        $routes = $this->app->make(RouteRegistrarContract::class);
        if (! $routes instanceof RouteRegistrarContract) {
            throw new DocsBindingException('Route registrar binding must resolve to a RouteRegistrarContract instance.');
        }

        $routes->get('/_octane/api', [ApiDocsController::class, 'index'])->name('octane.api.index');
        $routes->get('/_octane/api/{path*}', [ApiDocsController::class, 'show'])->name('octane.api.show');
    }

    private function registerCommands(): void
    {
        $commands = $this->app->make(CommandRegistryContract::class);
        if (! $commands instanceof CommandRegistryContract) {
            throw new DocsBindingException('Command registry binding must resolve to a CommandRegistryContract instance.');
        }

        $commands->register(ApiDocsGenerateCommand::class);
    }
}
