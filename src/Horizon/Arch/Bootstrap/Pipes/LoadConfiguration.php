<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Config\ConfigRepository;
use Horizon\Arch\Exceptions\BootstrapException;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Support\Pipeline\PipeInterface;

class LoadConfiguration implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $configPath = $payload->app->make('path.config');
        if (! is_string($configPath)) {
            throw new BootstrapException('Configuration path binding must resolve to a string.');
        }

        $repository = new ConfigRepository;

        $files = glob($configPath.'/*.php') ?: [];

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $repository->set($key, require $file);
        }

        $payload->app->instance(
            ConfigRepositoryContract::class,
            $repository
        );

        return $next($payload);
    }
}
