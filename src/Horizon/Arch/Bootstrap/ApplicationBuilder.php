<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap;

use Closure;
use Horizon\Arch\Application;
use Horizon\Arch\Bootstrap\Pipes\ApplyBuilderCallbacks;
use Horizon\Arch\Bootstrap\Pipes\BindApplicationAliases;
use Horizon\Arch\Bootstrap\Pipes\BindApplicationPaths;
use Horizon\Arch\Bootstrap\Pipes\BootProviders;
use Horizon\Arch\Bootstrap\Pipes\LoadConfiguration;
use Horizon\Arch\Bootstrap\Pipes\LoadEnvironment;
use Horizon\Arch\Bootstrap\Pipes\RegisterCoreBindings;
use Horizon\Arch\Bootstrap\Pipes\RegisterExceptionHandling;
use Horizon\Arch\Bootstrap\Pipes\RegisterProviders;
use Horizon\Contracts\Arch\Application\ApplicationBuilderContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;
use Horizon\Support\Pipeline\Pipeline;

class ApplicationBuilder implements ApplicationBuilderContract
{
    protected ?Closure $environment = null;

    protected ?Closure $paths = null;

    /**
     * @var string|array<class-string<ServiceProviderContract>>
     */
    protected string|array $providers = [];

    /**
     * @var list<string>
     */
    protected array $webRoutes = [];

    /**
     * @var list<string>
     */
    protected array $apiRoutes = [];

    protected ?Closure $middleware = null;

    protected ?Closure $exceptions = null;

    public function __construct(
        public Application $app
    ) {}

    /**
     * SET APPLICATION BUILDER CALLBACKS
     */
    public function withEnvironment(Closure $callback): static
    {
        $callback($this->app);

        return $this;
    }

    public function withPaths(Closure $callback): static
    {
        $callback($this->app);

        return $this;
    }

    /**
     * @param  string|array<class-string<ServiceProviderContract>>  $providers
     */
    public function withProviders(string|array $providers = []): ApplicationBuilder
    {
        $this->providers = $providers;

        return $this;
    }

    public function withRouting(?string $web = null, ?string $api = null): ApplicationBuilder
    {
        $this->webRoutes = array_filter([$web]);
        $this->apiRoutes = array_filter([$api]);

        return $this;
    }

    public function withMiddleware(callable $callback): ApplicationBuilder
    {
        $this->middleware = $callback(...);

        return $this;
    }

    public function withExceptions(callable $callback): ApplicationBuilder
    {
        $this->exceptions = $callback(...);

        return $this;
    }

    /**
     *  GET APPLICATION BUILDER CALLBACKS
     */
    public function getEnvironment(): ?Closure
    {
        return $this->environment;
    }

    public function getPaths(): ?Closure
    {
        return $this->paths;
    }

    public function getProviders(): string|array
    {
        return $this->providers;
    }

    /**
     * @return list<string>
     */
    public function getApiRoutes(): array
    {
        return $this->apiRoutes;
    }

    /**
     * @return list<string>
     */
    public function getWebRoutes(): array
    {
        return $this->webRoutes;
    }

    public function getMiddleware(): ?Closure
    {
        return $this->middleware;
    }

    public function getExceptions(): ?Closure
    {
        return $this->exceptions;
    }

    /**
     * CREATE APPLICATION
     */
    public function create(): Application
    {
        new Pipeline($this->app->getContainer())
            ->send($this)
            ->through([
                /** Bind Application Paths and Register Core Bindings */
                BindApplicationPaths::class,
                RegisterCoreBindings::class,

                /** Register Exception Handling */
                RegisterExceptionHandling::class,

                /** Load Environment, Configuration */
                LoadEnvironment::class,
                LoadConfiguration::class,

                /** Register Application Aliases */
                BindApplicationAliases::class,

                /** Register ServiceProviders and Boot ServiceProviders */
                RegisterProviders::class,
                BootProviders::class,

                /** Apply Application Builder Callbacks */
                ApplyBuilderCallbacks::class,
            ])
            ->then(fn (ApplicationBuilder $builder) => $builder->app);

        return $this->app;
    }
}
