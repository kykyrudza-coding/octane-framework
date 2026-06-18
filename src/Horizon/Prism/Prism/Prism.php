<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;
use Horizon\Contracts\Prism\Prism\Compiler\DirectiveRegistryContract;
use Horizon\Contracts\Prism\Prism\PrismContract;
use Horizon\Contracts\Prism\ViewContract;
use Horizon\Contracts\Prism\ViewFactoryContract;
use Horizon\Prism\Prism\Component\ComponentRegistry;

final readonly class Prism implements PrismContract
{
    public function __construct(
        private ViewFactoryContract       $viewFactory,
        private DirectiveRegistryContract $directives,
        private ComponentRegistry         $components,
    ) {}

    /**
     * Register a directive.
     *
     * @param  DirectiveContract|callable(string): string  $directive
     */
    public function directive(DirectiveContract|callable $directive, ?string $name = null): void
    {
        $this->directives->register($directive, $name);
    }

    /**
     * Register a component alias pointing to a class.
     *
     * Example: $prism->component('Button', ButtonComponent::class);
     */
    public function component(string $alias, string $class): void
    {
        $this->components->registerAlias($alias, $class);
    }

    /**
     * Render a view by dot-notation name.
     *
     * Example: $prism->render('pages.home', ['user' => $user]);
     */
    public function render(string $view, array $data = []): string
    {
        return $this->viewFactory->make($view, $data)->render();
    }

    /**
     * Create a View instance without immediately rendering it.
     */
    public function view(string $view, array $data = []): ViewContract
    {
        return $this->viewFactory->make($view, $data);
    }

    /**
     * Share data across all views.
     *
     * @param  string|array<string, mixed>  $key
     */
    public function share(string|array $key, mixed $value = null): void
    {
        $this->viewFactory->share($key, $value);
    }

    public function exists(string $view): bool
    {
        return $this->viewFactory->exists($view);
    }
}
