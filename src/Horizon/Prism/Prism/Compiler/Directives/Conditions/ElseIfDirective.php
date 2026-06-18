<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Conditions;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

final class ElseIfDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'elseif';
    }

    public function compile(string $expression): string
    {
        return "<?php elseif ($expression): ?>";
    }
}
