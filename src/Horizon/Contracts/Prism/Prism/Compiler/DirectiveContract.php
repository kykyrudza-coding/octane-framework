<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Prism\Compiler;

interface DirectiveContract
{
    public function name(): string;

    public function compile(string $expression): string;
}
