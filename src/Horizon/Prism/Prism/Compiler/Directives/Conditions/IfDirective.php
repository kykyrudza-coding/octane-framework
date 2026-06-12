<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Conditions;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;

final class IfDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'if';
    }

    public function compile(string $expression): string
    {
        return "<?php if ($expression): ?>";
    }
}
