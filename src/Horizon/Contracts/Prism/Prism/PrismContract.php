<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Prism;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

interface PrismContract
{
    public function directive(DirectiveContract|callable $directive, ?string $name = null): void;

    public function component(string $alias, string $class): void;

    public function render(string $view, array $data = []): string;
}
