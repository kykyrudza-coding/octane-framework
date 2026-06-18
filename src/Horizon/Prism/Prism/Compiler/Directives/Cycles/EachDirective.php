<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Cycles;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

final class EachDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'each';
    }

    public function compile(string $expression): string
    {
        return "<?php foreach({$expression}): ?>";
    }
}
