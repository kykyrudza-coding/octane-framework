<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Prism\Compiler;

interface PrismCompilerContract
{
    public function compile(string $path): string;

    public function isExpired(string $path): bool;

    public function compiledPath(string $path): string;
}
