<?php

declare(strict_types=1);

namespace Horizon\Contracts\Arch\Application;

use Closure;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;

interface ApplicationContract
{
    public function getContainer(): ContainerContract;

    /**
     * @return array<class-string<\Horizon\Contracts\Support\Providers\ServiceProviderContract>, \Horizon\Contracts\Support\Providers\ServiceProviderContract>
     */
    public function getProviders(): array;

    public static function version(): string;

    /**
     * @param  array<class-string<\Horizon\Contracts\Support\Providers\ServiceProviderContract>, \Horizon\Contracts\Support\Providers\ServiceProviderContract>  $providers
     */
    public function setProviders(array $providers): void;

    public function registerProvider(ServiceProviderContract $provider): void;

    public function bootProviders(): void;

    public function handleRequest(RequestContextContract $requestContext): static;

    public function run(): void;

    public function runCli(array $argv): int;

    public static function configure(string $basePath): ApplicationBuilderContract;

    public function bind(string $abstract, callable|string $concrete): void;

    public function singleton(string $abstract, callable|string $concrete): void;

    public function instance(string $abstract, object $instance): void;

    public function make(string $abstract): mixed;

    public function has(string $abstract): bool;

    public function bindPath(string $abstract, string $path): void;

    public function bindAlias(string $alias, string $abstract): void;

    public function terminating(Closure $callback): static;

    public function terminate(): void;
}
