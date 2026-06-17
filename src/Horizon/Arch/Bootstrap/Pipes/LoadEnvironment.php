<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Dotenv\Dotenv;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Support\Pipeline\PipeInterface;

class LoadEnvironment implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $app = $payload->app;

        $pathToBaseEnv = $app->getEnvironmentFile();

        $dir = dirname($pathToBaseEnv);
        $file = basename($pathToBaseEnv);

        Dotenv::createImmutable($dir, $file)->load();

        $env = $_ENV['APP_ENV'] ?? 'production';
        $env = is_string($env) ? $env : 'production';
        $app->setEnvironment($env);

        $specific = match ($env) {
            'development' => $app->getDevelopmentEnvironmentFile(),
            'testing' => $app->getTestingEnvironmentFile(),
            'local' => $app->getLocalEnvironmentFile(),
            default => $app->getProductionEnvironmentFile(),
        };

        if (is_file($specific)) {
            Dotenv::createMutable(dirname($specific), basename($specific))->load();
        }

        return $next($payload);
    }
}
