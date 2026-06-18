<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Exception\HandlerContract;
use Horizon\Contracts\Http\Collection\MiddlewareCollectionContract;
use Horizon\Contracts\Routing\RouteRegistrarContract;
use Horizon\Support\Pipeline\PipeInterface;

class ApplyBuilderCallbacks implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $this->route($payload);
        $this->middleware($payload);
        $this->exception($payload);

        return $next($payload);
    }

    protected function route(ApplicationBuilder $payload): void
    {
        $registrar = $payload->app->make(RouteRegistrarContract::class);
        if (! $registrar instanceof RouteRegistrarContract) {
            throw new BindingResolutionException('Route registrar binding must resolve to a RouteRegistrarContract instance.');
        }

        foreach ($this->routeFiles($payload, 'web') as $routeFile) {
            $this->loadRouteFile($registrar, $payload, 'web', $routeFile);
        }

        foreach ($this->routeFiles($payload, 'api') as $routeFile) {
            $this->loadRouteFile($registrar, $payload, 'api', $routeFile);
        }
    }

    protected function middleware(ApplicationBuilder $payload): void
    {
        $collection = $payload->app->make(MiddlewareCollectionContract::class);
        if (! $collection instanceof MiddlewareCollectionContract) {
            throw new BindingResolutionException('Middleware collection binding must resolve to a MiddlewareCollectionContract instance.');
        }

        if ($callback = $payload->getMiddleware()) {
            $callback($collection);
        }

        $payload->app->instance(
            MiddlewareCollectionContract::class,
            $collection
        );
    }

    protected function exception(ApplicationBuilder $payload): void
    {
        if ($payload->getExceptions() instanceof Closure) {
            $handler = $payload->app->make(HandlerContract::class);
            if (! $handler instanceof HandlerContract) {
                throw new BindingResolutionException('Exception handler binding must resolve to a HandlerContract instance.');
            }

            ($payload->getExceptions())(
                $handler
            );
        }
    }

    /**
     * @return list<string>
     */
    private function routeFiles(ApplicationBuilder $payload, string $group): array
    {
        $files = $group === 'web'
            ? $payload->getWebRoutes()
            : $payload->getApiRoutes();

        if ($files !== []) {
            return $files;
        }

        return $this->stringList(
            $this->config($payload, "routing.files.$group", []),
        );
    }

    private function loadRouteFile(
        RouteRegistrarContract $registrar,
        ApplicationBuilder $payload,
        string $group,
        string $routeFile,
    ): void {
        if (! is_file($routeFile)) {
            return;
        }

        $groupConfig = $this->arrayConfig($payload, "routing.groups.$group");

        $registrar->setCurrentGroup($group);

        try {
            $scoped = $registrar;
            $prefix = $groupConfig['prefix'] ?? '';
            $name = $groupConfig['name'] ?? '';
            $middleware = $this->stringList($groupConfig['middleware'] ?? []);

            if (is_string($prefix) && $prefix !== '') {
                $scoped = $scoped->prefix($prefix);
            }

            if (is_string($name) && $name !== '') {
                $scoped = $scoped->name($name);
            }

            if ($middleware !== []) {
                $scoped = $scoped->middleware($middleware);
            }

            $scoped->group(static function () use ($routeFile): void {
                require $routeFile;
            });
        } finally {
            $registrar->clearCurrentGroup();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function arrayConfig(ApplicationBuilder $payload, string $key): array
    {
        $value = $this->config($payload, $key, []);

        return is_array($value) ? $value : [];
    }

    private function config(ApplicationBuilder $payload, string $key, mixed $default = null): mixed
    {
        $config = $payload->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return $default;
        }

        return $config->get($key, $default);
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (is_string($value) && $value !== '') {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            $value,
            static fn (mixed $item): bool => is_string($item) && $item !== '',
        ));
    }
}
