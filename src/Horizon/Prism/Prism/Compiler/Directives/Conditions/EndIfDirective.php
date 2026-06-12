<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Conditions;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;

final class EndIfDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'endif';
    }

    public function compile(string $expression): string
    {
        return '<?php endif; ?>';
    }
}
