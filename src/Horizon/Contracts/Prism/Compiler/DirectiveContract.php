<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Compiler;

interface DirectiveContract
{
    public function name(): string;

    public function compile(string $expression): string;
}
