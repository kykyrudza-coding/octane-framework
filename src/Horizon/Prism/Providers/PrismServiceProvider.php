<?php

declare(strict_types=1);

namespace Horizon\Prism\Providers;

use Horizon\Support\Providers\ServiceProvider;
use Horizon\Contracts\Prism\Compiler\DirectiveRegistryContract;
use Horizon\Contracts\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Component\ComponentRegistryContract;
use Horizon\Contracts\Prism\Component\ComponentResolverContract;
use Horizon\Contracts\Prism\Engine\PrismEngineContract;
use Horizon\Contracts\Prism\PrismContract;
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
    }

    // ─── Bindings ─────────────────────────────────────────────────────────

    private function registerDirectiveRegistry(): void
    {
        $this->app->singleton(
            DirectiveRegistryContract::class,
            fn () => new DirectiveRegistry(),
        );

        $this->app->bindAlias('prism.directives', DirectiveRegistryContract::class);
    }

    private function registerCompiler(): void
    {
        $this->app->singleton(
            PrismCompilerContract::class,
            fn ($app) => new PrismCompiler(
                directives: $app->make(DirectiveRegistryContract::class),
                cachePath: $app->make('path.cache') . DIRECTORY_SEPARATOR . 'prism',
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
            fn () => new ComponentRegistry(),
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
                viewsPath: $app->make('path.ui') . DIRECTORY_SEPARATOR . 'views',
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
                viewsPath: $app->make('path.ui') . DIRECTORY_SEPARATOR . 'views',
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
     * @return list<\Horizon\Contracts\Prism\Compiler\DirectiveContract>
     */
    private function builtinDirectives(): array
    {
        return [
            // Conditions
            new IfDirective(),
            new ElseIfDirective(),
            new ElseDirective(),
            new EndIfDirective(),

            // Cycles
            new EachDirective(),
            new EndForeachDirective(),

            // Templates
            new LayoutDirective(),
            new BlockDirective(),
            new EndBlockDirective(),
            new SlotDirective(),
            new ImportDirective(),
        ];
    }
}
