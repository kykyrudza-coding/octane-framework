<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Conditions;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

final class ElseDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'else';
    }

    public function compile(string $expression): string
    {
        return '<?php else: ?>';
    }
}
