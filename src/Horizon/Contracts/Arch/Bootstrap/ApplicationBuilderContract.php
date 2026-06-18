<?php

declare(strict_types=1);

namespace Horizon\Contracts\Arch\Bootstrap;

use Closure;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;

interface ApplicationBuilderContract
{
    public function withMiddleware(callable $callback): ApplicationBuilderContract;

    public function withRouting(?string $web = null, ?string $api = null): ApplicationBuilderContract;

    public function withExceptions(callable $callback): ApplicationBuilderContract;

    /**
     * @param  string|array<class-string<ServiceProviderContract>>  $providers
     */
    public function withProviders(string|array $providers = []): ApplicationBuilderContract;

    /**
     * @return string|array<class-string<ServiceProviderContract>>
     */
    public function getProviders(): string|array;

    /**
     * @return list<string>
     */
    public function getApiRoutes(): array;

    /**
     * @return list<string>
     */
    public function getWebRoutes(): array;

    public function getMiddleware(): ?Closure;

    public function getExceptions(): ?Closure;

    public function create(): ApplicationContract;
}
