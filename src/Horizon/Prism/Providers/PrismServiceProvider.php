<?php

declare(strict_types=1);

namespace Horizon\Prism\Providers;

use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;
use Horizon\Contracts\Prism\Prism\Compiler\DirectiveRegistryContract;
use Horizon\Contracts\Prism\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Prism\Component\ComponentRegistryContract;
use Horizon\Contracts\Prism\Prism\Component\ComponentResolverContract;
use Horizon\Contracts\Prism\Prism\Engine\PrismEngineContract;
use Horizon\Contracts\Prism\Prism\PrismContract;
use Horizon\Contracts\Prism\ViewFactoryContract;
use Horizon\Prism\Prism\Compiler\DirectiveRegistry;
use Horizon\Prism\Prism\Compiler\Directives\Conditions\ElseDirective;
use Horizon\Prism\Prism\Compiler\Directives\Conditions\ElseIfDirective;
use Horizon\Prism\Prism\Compiler\Directives\Conditions\EndIfDirective;
use Horizon\Prism\Prism\Compiler\Directives\Conditions\IfDirective;
use Horizon\Prism\Prism\Compiler\Directives\Cycles\EachDirective;
use Horizon\Prism\Prism\Compiler\Directives\Cycles\EndForeachDirective;
use Horizon\Prism\Prism\Compiler\Directives\Templates\BlockDirective;
use Horizon\Prism\Prism\Compiler\Directives\Templates\EndBlockDirective;
use Horizon\Prism\Prism\Compiler\Directives\Templates\ImportDirective;
use Horizon\Prism\Prism\Compiler\Directives\Templates\LayoutDirective;
use Horizon\Prism\Prism\Compiler\Directives\Templates\SlotDirective;
use Horizon\Prism\Prism\Compiler\PrismCompiler;
use Horizon\Prism\Prism\Component\ComponentRegistry;
use Horizon\Prism\Prism\Component\ComponentResolver;
use Horizon\Prism\Prism\Engine\PrismEngine;
use Horizon\Prism\Prism\Prism;
use Horizon\Prism\ViewFactory;
use Horizon\Support\Providers\ServiceProvider;

class PrismServiceProvider extends ServiceProvider
{
    public static int $priority = 50;

    public function register(): void
    {
        $this->registerDirectiveRegistry();
        $this->registerCompiler();
        $this->registerComponentRegistry();
        $this->registerComponentResolver();
        $this->registerEngine();
        $this->registerViewFactory();
        $this->registerPrism();
    }

    public function boot(): void
    {
        // Register built-in directives
        $registry = $this->app->make(DirectiveRegistryContract::class);

        foreach ($this->builtinDirectives() as $directive) {
            $registry->register($directive);
        }

        foreach ($this->configuredDirectives() as $name => $directive) {
            $registry->register($directive, is_string($name) ? $name : null);
        }

        $components = $this->app->make(ComponentRegistryContract::class);

        foreach ($this->configuredComponentAliases() as $alias => $component) {
            if (method_exists($components, 'registerAlias')) {
                $components->registerAlias($alias, $component);
            }
        }
    }

    // ─── Bindings ─────────────────────────────────────────────────────────

    private function registerDirectiveRegistry(): void
    {
        $this->app->singleton(
            DirectiveRegistryContract::class,
            fn () => new DirectiveRegistry,
        );

        $this->app->bindAlias('prism.directives', DirectiveRegistryContract::class);
    }

    private function registerCompiler(): void
    {
        $this->app->singleton(
            PrismCompilerContract::class,
            fn ($app) => new PrismCompiler(
                directives: $app->make(DirectiveRegistryContract::class),
                cachePath: $this->configString(
                    'prism.compiler.cache.path',
                    $app->make('path.cache').DIRECTORY_SEPARATOR.'prism',
                ),
                cacheEnabled: $this->configBool('prism.compiler.cache.enabled', true),
            ),
        );

        // Concrete class binding so PrismEngine can type-hint PrismCompiler directly
        $this->app->singleton(
            PrismCompiler::class,
            fn ($app) => $app->make(PrismCompilerContract::class),
        );

        $this->app->bindAlias('prism.compiler', PrismCompilerContract::class);
    }

    private function registerComponentRegistry(): void
    {
        $this->app->singleton(
            ComponentRegistryContract::class,
            fn () => new ComponentRegistry,
        );

        // Concrete class alias so ComponentResolver can type-hint it
        $this->app->singleton(
            ComponentRegistry::class,
            fn ($app) => $app->make(ComponentRegistryContract::class),
        );

        $this->app->bindAlias('prism.components', ComponentRegistryContract::class);
    }

    private function registerComponentResolver(): void
    {
        $this->app->singleton(
            ComponentResolverContract::class,
            fn ($app) => new ComponentResolver(
                registry: $app->make(ComponentRegistryContract::class),
            ),
        );

        $this->app->bindAlias('prism.resolver', ComponentResolverContract::class);
    }

    private function registerEngine(): void
    {
        $this->app->singleton(
            PrismEngineContract::class,
            fn ($app) => new PrismEngine(
                componentResolver: $app->make(ComponentResolverContract::class),
                compiler: $app->make(PrismCompiler::class),
                viewsPath: $this->configString(
                    'prism.views.path',
                    $app->make('path.ui').DIRECTORY_SEPARATOR.'views',
                ),
            ),
        );

        $this->app->bindAlias('prism.engine', PrismEngineContract::class);
    }

    private function registerViewFactory(): void
    {
        $this->app->singleton(
            ViewFactoryContract::class,
            fn ($app) => new ViewFactory(
                compiler: $app->make(PrismCompiler::class),
                engine: $app->make(PrismEngineContract::class),
                viewsPath: $this->configString(
                    'prism.views.path',
                    $app->make('path.ui').DIRECTORY_SEPARATOR.'views',
                ),
                extensions: $this->stringList(
                    $this->config('prism.views.extensions', ['.prism.php', '.php', '.html']),
                ),
            ),
        );

        $this->app->bindAlias('prism.view', ViewFactoryContract::class);
        $this->app->bindAlias('view', ViewFactoryContract::class);
    }

    private function registerPrism(): void
    {
        $this->app->singleton(
            PrismContract::class,
            fn ($app) => new Prism(
                viewFactory: $app->make(ViewFactoryContract::class),
                directives: $app->make(DirectiveRegistryContract::class),
                components: $app->make(ComponentRegistry::class),
            ),
        );

        $this->app->bindAlias('prism', PrismContract::class);
    }

    // ─── Built-in directives ──────────────────────────────────────────────

    /**
     * @return list<DirectiveContract>
     */
    private function builtinDirectives(): array
    {
        return [
            // Conditions
            new IfDirective,
            new ElseIfDirective,
            new ElseDirective,
            new EndIfDirective,

            // Cycles
            new EachDirective,
            new EndForeachDirective,

            // Templates
            new LayoutDirective,
            new BlockDirective,
            new EndBlockDirective,
            new SlotDirective,
            new ImportDirective,
        ];
    }

    /**
     * @return array<string, class-string>
     */
    private function configuredComponentAliases(): array
    {
        return $this->stringMap($this->configArray('prism.components.aliases'));
    }

    /**
     * @return array<int|string, DirectiveContract|callable>
     */
    private function configuredDirectives(): array
    {
        $directives = [];

        foreach ($this->configArray('prism.directives') as $name => $directive) {
            if (! is_string($directive) || ! class_exists($directive)) {
                continue;
            }

            $instance = new $directive;

            if ($instance instanceof DirectiveContract || is_callable($instance)) {
                $directives[$name] = $instance;
            }
        }

        return $directives;
    }

    private function configBool(string $key, bool $default): bool
    {
        $value = $this->config($key, $default);

        return is_bool($value) ? $value : $default;
    }

    private function configString(string $key, string $default): string
    {
        $value = $this->config($key, $default);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function configArray(string $key): array
    {
        $value = $this->config($key, []);

        return is_array($value) ? $value : [];
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

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, class-string>
     */
    private function stringMap(array $value): array
    {
        $map = [];

        foreach ($value as $alias => $class) {
            if (is_string($alias) && is_string($class) && $class !== '') {
                $map[$alias] = $class;
            }
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            $value,
            static fn (mixed $item): bool => is_string($item) && $item !== '',
        ));
    }
}
