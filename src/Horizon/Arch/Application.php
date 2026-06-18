<?php

declare(strict_types=1);

namespace Horizon\Arch;

use Closure;
use Composer\Autoload\ClassLoader;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Arch\Exceptions\InvalidApplicationStateException;
use Horizon\Arch\Traits\ApplicationEnvironment;
use Horizon\Arch\Traits\ManagesApplicationPaths;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Arch\ContainerContract;
use Horizon\Contracts\Arch\Http\HttpKernelContract;
use Horizon\Contracts\Console\ConsoleKernelContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;

class Application implements ApplicationContract
{
    use ApplicationEnvironment, ManagesApplicationPaths;

    protected static string $octaneVersion = '2.0.0';

    protected static string $octaneKernelVersion = '0.1.0';

    protected ContainerContract $container;

    protected static ?self $instance = null;

    protected ?RequestContextContract $requestContext = null;

    protected bool $runningInConsole = false;

    protected bool $booted = false;
    /**
     * @var array<class-string<ServiceProviderContract>, ServiceProviderContract>
     */
    protected array $providers = [];

    /**
     * @var list<Closure(self): mixed>
     */
    protected array $terminateCallbacks = [];

    /**
     *  APPLICATION PATHS
     */
    protected string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->container = new Container;
        static::$instance = $this;

        if ($basePath) {
            $this->setBasePath($basePath);
        }
    }

    public static function version(): string
    {
        return 'Octane v'.static::$octaneVersion.' (Horizon kernel v'.static::$octaneKernelVersion.')';
    }

    public static function getInstance(): self
    {
        if (static::$instance === null) {
            throw new InvalidApplicationStateException('Application has not been initialized.');
        }

        return static::$instance;
    }

    /**
     *  GET APPLICATION CONTAINER
     */
    public function getContainer(): ContainerContract
    {
        return $this->container;
    }

    /**
     *  GET|SET APPLICATION PROVIDERS
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function setProviders(array $providers): void
    {
        $this->providers = $providers;
    }

    public function registerProvider(ServiceProviderContract $provider): void
    {
        $class = $provider::class;

        if (isset($this->providers[$class])) {
            return;
        }

        $provider->register();

        $this->providers[$class] = $provider;
    }

    public function bootProviders(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    /**
     *  APPLICATION HANDLE REQUEST
     */
    public function handleRequest(RequestContextContract $requestContext): static
    {
        $this->requestContext = $requestContext;

        return $this;
    }

    /**
     *  APPLICATION RUN
     */
    public function run(): void
    {
        $kernel = $this->make(HttpKernelContract::class);

        $this->runningInConsole = false;

        if (! $kernel instanceof HttpKernelContract) {
            throw new BindingResolutionException('HTTP kernel binding must resolve to an HttpKernelContract instance.');
        }

        if (! isset($this->requestContext)) {
            throw new InvalidApplicationStateException('Request context has not been set.');
        }

        $response = $kernel->handle($this->requestContext);

        $response->send();

        $kernel->terminate($this->requestContext, $response);
    }

    public function runCli(array $argv): int
    {
        $this->runningInConsole = true;

        $kernel = $this->make(ConsoleKernelContract::class);

        $exitCode = $kernel->handle($argv);

        $this->terminate();

        return $exitCode;
    }

    /**
     *  BOOTSTRAP APPLICATION
     */
    public static function configure(string $basePath): ApplicationBuilder
    {
        return new ApplicationBuilder(new self($basePath));
    }

    public static function inferBasePath(): string
    {
        if (isset($_ENV['BASE_PATH']) && is_string($_ENV['BASE_PATH'])) {
            return $_ENV['BASE_PATH'];
        }

        if (isset($_SERVER['APP_BASE_PATH']) && is_string($_SERVER['APP_BASE_PATH'])) {
            return $_SERVER['APP_BASE_PATH'];
        }

        $loaderPaths = array_values(array_filter(
            array_keys(ClassLoader::getRegisteredLoaders()),
            static fn (string $path): bool => ! str_starts_with($path, 'phar://'),
        ));

        if ($loaderPaths === []) {
            throw new InvalidApplicationStateException('Unable to infer application base path from Composer loaders.');
        }

        return dirname($loaderPaths[0]);
    }

    public function setBasePath(string $basePath): static
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    public function basePath(string $path = ''): string
    {
        return $this->joinPaths($this->basePath, $path);
    }

    /**
     *  CONTAINER OPEN API
     */
    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->container->bind($abstract, $concrete);
    }

    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->container->instance($abstract, $instance);
    }

    public function make(string $abstract): mixed
    {
        return $this->container->make($abstract);
    }

    public function has(string $abstract): bool
    {
        return $this->container->has($abstract);
    }

    public function bindPath(string $abstract, string $path): void
    {
        $this->container->bindPath($abstract, $path);
    }

    public function bindAlias(string $alias, string $abstract): void
    {
        $this->container->bindAlias($alias, $abstract);
    }

    public function terminating(Closure $callback): static
    {
        $this->terminateCallbacks[] = $callback;

        return $this;
    }

    public function terminate(): void
    {
        foreach ($this->terminateCallbacks as $callback) {
            $callback($this);
        }
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}
