<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Cycles;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;

final class EndForeachDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'endforeach';
    }

    public function compile(string $expression): string
    {
        return '<?php endforeach; ?>';
    }
}
