<?php

declare(strict_types=1);

namespace Horizon\Docs\Providers;

use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
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
        if ($this->config('docs.api.enabled', true) !== true) {
            return;
        }

        $routes = $this->app->make(RouteRegistrarContract::class);
        if (! $routes instanceof RouteRegistrarContract) {
            throw new DocsBindingException('Route registrar binding must resolve to a RouteRegistrarContract instance.');
        }

        $route = $this->config('docs.api.route', '/_octane/api');
        $route = is_string($route) && $route !== '' ? '/'.trim($route, '/') : '/_octane/api';

        $routes->get($route, [ApiDocsController::class, 'index'])->name('octane.api.index');
        $routes->get($route.'/{path*}', [ApiDocsController::class, 'show'])->name('octane.api.show');
    }

    private function registerCommands(): void
    {
        $commands = $this->app->make(CommandRegistryContract::class);
        if (! $commands instanceof CommandRegistryContract) {
            throw new DocsBindingException('Command registry binding must resolve to a CommandRegistryContract instance.');
        }

        $commands->register(ApiDocsGenerateCommand::class);
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if (! $this->app->has(ConfigRepositoryContract::class)) {
            return $default;
        }

        $config = $this->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return $default;
        }

        return $config->get($key, $default);
    }
}
