<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Engine;

use Horizon\Contracts\Prism\Prism\Component\ComponentResolverContract;
use Horizon\Contracts\Prism\Prism\Engine\PrismEngineContract;
use Horizon\Contracts\Prism\Prism\Compiler\PrismCompilerContract;
use Horizon\Prism\Exceptions\TemplateCompilationException;

final class PrismEngine implements PrismEngineContract
{
    public function __construct(
        private readonly ComponentResolverContract $componentResolver,
        private readonly PrismCompilerContract    $compiler,
        private readonly string                   $viewsPath,
    ) {}

    public function render(string $compiledPath, array $data = []): string
    {
        if (!file_exists($compiledPath)) {
            throw new TemplateCompilationException("Compiled template not found: $compiledPath");
        }

        $context = new TemplateContext(
            engine: $this,
            componentResolver: $this->componentResolver,
            compiler: $this->compiler,
            viewsPath: $this->viewsPath,
        );

        return $context->evaluate($compiledPath, $data);
    }
}
