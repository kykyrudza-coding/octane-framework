<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Exceptions\BootstrapException;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;
use Horizon\Support\Pipeline\PipeInterface;

class RegisterProviders implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $all = array_merge(
            $this->discoverProviders(),
            $this->configuredProviders($payload),
        );

        usort($all, static fn (string $a, string $b): int => $b::$priority <=> $a::$priority);

        foreach ($all as $providerClass) {
            $payload->app->registerProvider(new $providerClass($payload->app));
        }

        return $next($payload);
    }

    /**
     * @return list<class-string<ServiceProviderContract>>
     */
    protected function configuredProviders(ApplicationBuilder $payload): array
    {
        $providers = $payload->getProviders();

        if ($providers === [] || $providers === '') {
            $providers = $this->configuredProviderListFromConfig($payload);
        }

        if (is_string($providers)) {
            $providers = require $providers;
        }

        if (! is_array($providers)) {
            throw new BootstrapException('Configured providers must be an array of service provider class names.');
        }

        return $this->filterProviderClasses($providers);
    }

    /**
     * @return array<mixed>
     */
    private function configuredProviderListFromConfig(ApplicationBuilder $payload): array
    {
        $config = $payload->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return [];
        }

        $providers = $config->get('app.providers', []);

        return is_array($providers) ? $providers : [];
    }

    /**
     * @return list<class-string<ServiceProviderContract>>
     */
    protected function discoverProviders(): array
    {
        $providers = [];

        $pattern = __DIR__.'/../../../*/components.json';

        $files = glob($pattern) ?: [];

        foreach ($files as $file) {
            $data = json_decode((string) file_get_contents($file), true);

            if (is_array($data) && isset($data['provider'])) {
                $providers[] = $data['provider'];
            }
        }

        return $this->filterProviderClasses($providers);
    }

    /**
     * @param  array<mixed>  $providers
     * @return list<class-string<ServiceProviderContract>>
     */
    protected function filterProviderClasses(array $providers): array
    {
        $classes = [];

        foreach ($providers as $provider) {
            if (is_string($provider) && is_subclass_of($provider, ServiceProviderContract::class)) {
                /** @var class-string<ServiceProviderContract> $provider */
                $classes[] = $provider;
            }
        }

        return $classes;
    }
}
