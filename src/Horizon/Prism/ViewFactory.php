<?php

declare(strict_types=1);

namespace Horizon\Prism;

use Horizon\Contracts\Prism\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Prism\Engine\PrismEngineContract;
use Horizon\Contracts\Prism\ViewContract;
use Horizon\Contracts\Prism\ViewFactoryContract;
use Horizon\Prism\Exceptions\ViewNotFoundException;

final class ViewFactory implements ViewFactoryContract
{
    /** @var array<string, mixed> */
    private array $shared = [];

    /**
     * Supported view file extensions in resolution order.
     *
     * @var list<string>
     */
    private array $extensions = ['.prism.php', '.php', '.html'];

    public function __construct(
        private readonly PrismCompilerContract $compiler,
        private readonly PrismEngineContract $engine,
        private readonly string $viewsPath,
        array $extensions = ['.prism.php', '.php', '.html'],
    ) {
        $this->extensions = array_values(array_filter(
            $extensions,
            static fn (mixed $extension): bool => is_string($extension) && $extension !== '',
        ));

        if ($this->extensions === []) {
            $this->extensions = ['.prism.php', '.php', '.html'];
        }
    }

    public function make(string $view, array $data = []): ViewContract
    {
        $path = $this->resolvePath($view);

        $compiledPath = $this->compiler->compile($path);

        $mergedData = array_merge($this->shared, $data);

        $rendered = $this->engine->render($compiledPath, $mergedData);

        // Return an already-rendered view wrapped in a simple ValueView
        return new RenderedView($rendered);
    }

    public function exists(string $view): bool
    {
        try {
            $this->resolvePath($view);

            return true;
        } catch (ViewNotFoundException) {
            return false;
        }
    }

    /**
     * @param  string|array<string, mixed>  $key
     */
    public function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } else {
            $this->shared[$key] = $value;
        }
    }

    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * Resolves a dot-notation view name to an absolute file path.
     * e.g. "layouts.app" → "{viewsPath}/layouts/app.prism.php"
     */
    private function resolvePath(string $view): string
    {
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $view);
        $base = rtrim($this->viewsPath, '/\\');

        foreach ($this->extensions as $ext) {
            $path = $base.DIRECTORY_SEPARATOR.$relative.$ext;
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new ViewNotFoundException(
            "View '$view' not found in path '$this->viewsPath'."
        );
    }
}
