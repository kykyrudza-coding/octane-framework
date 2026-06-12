<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Engine;

use Horizon\Contracts\Prism\Component\ComponentResolverContract;
use Horizon\Contracts\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Engine\PrismEngineContract;
use RuntimeException;
use Throwable;

/**
 * Holds the rendering state for a single template evaluation.
 *
 * Exposed to compiled templates as $__prism, providing:
 *   - layout('name')          — extend a parent layout
 *   - startBlock('name')      — open a named block (#block)
 *   - endBlock()              — close the current block (#endblock)
 *   - slot('name')            — output a named block inside a layout (#slot)
 *   - import('name', $data)   — include and render a sub-view
 *   - component('Name', $props, $slot) — render a component
 */
final class TemplateContext
{
    /** @var array<string, string> Named block content */
    private array $blocks = [];

    private ?string $currentBlock = null;

    private ?string $layoutView = null;

    /** @var list<string> Supported extensions in resolution order */
    private array $extensions = ['.prism.php', '.php', '.html'];

    public function __construct(
        private readonly PrismEngineContract              $engine,
        private readonly ComponentResolverContract $componentResolver,
        private readonly PrismCompilerContract    $compiler,
        private readonly string                   $viewsPath,
    ) {}

    /**
     * Evaluate a compiled template file, returning its rendered output.
     *
     * @param  array<string, mixed>  $data
     */
    public function evaluate(string $__path, array $__data): string
    {
        $__prism = $this;

        extract($__data, EXTR_SKIP);

        ob_start();

        try {
            include $__path;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $output = (string) ob_get_clean();

        // If this template declared a layout, render the layout now
        if ($this->layoutView !== null) {
            return $this->renderLayout($this->layoutView, $__data);
        }

        return $output;
    }

    // ─── Template Inheritance ─────────────────────────────────────────────

    /**
     * Declare that this template extends a layout.
     * Called by compiled #layout('name') directives.
     */
    public function layout(string $view): void
    {
        $this->layoutView = $view;
    }

    /**
     * Start capturing a named block.
     * Called by compiled #block('name') directives.
     */
    public function startBlock(string $name): void
    {
        $this->currentBlock = $name;
        ob_start();
    }

    /**
     * Stop capturing the current block and store it.
     * Called by compiled #endblock directives.
     */
    public function endBlock(): void
    {
        if ($this->currentBlock === null) {
            throw new RuntimeException('endBlock() called without a matching startBlock().');
        }

        $this->blocks[$this->currentBlock] = (string) ob_get_clean();
        $this->currentBlock = null;
    }

    /**
     * Output a named block inside a layout.
     * Called by compiled #slot('name') directives.
     */
    public function slot(string $name): string
    {
        return $this->blocks[$name] ?? '';
    }

    // ─── Sub-views ────────────────────────────────────────────────────────

    /**
     * Include and render a sub-view with optional data.
     * Called by compiled #import('name') directives.
     *
     * @param  array<string, mixed>  $data
     */
    public function import(string $view, array $data = []): string
    {
        $path = $this->resolveViewPath($view);
        $compiled = $this->compiler->compile($path);

        $childContext = new self(
            $this->engine,
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath,
        );

        return $childContext->evaluate($compiled, $data);
    }

    // ─── Components ───────────────────────────────────────────────────────

    /**
     * Render a registered component.
     *
     * @param  array<string, mixed>  $props
     */
    public function component(string $alias, array $props = [], string $slot = ''): string
    {
        $component = $this->componentResolver->resolve($alias, $props);

        if ($slot !== '' && method_exists($component, 'withSlot')) {
            $component = $component->withSlot($slot);
        }

        return $component->render();
    }

    // ─── Internals ────────────────────────────────────────────────────────

    private function renderLayout(string $view, array $data): string
    {
        $path = $this->resolveViewPath($view);
        $compiled = $this->compiler->compile($path);

        $layoutContext = new self(
            $this->engine,
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath,
        );

        // Pass blocks captured by child into the layout context
        $layoutContext->blocks = $this->blocks;

        return $layoutContext->evaluate($compiled, $data);
    }

    /**
     * Resolves a dot-notation view name to an absolute source file path.
     * e.g. "layouts.app" → "{viewsPath}/layouts/app.prism.php"
     */
    private function resolveViewPath(string $view): string
    {
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $view);
        $base = rtrim($this->viewsPath, '/\\');

        foreach ($this->extensions as $ext) {
            $path = $base . DIRECTORY_SEPARATOR . $relative . $ext;
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new RuntimeException(
            "View '$view' not found in '{$this->viewsPath}'."
        );
    }
}
