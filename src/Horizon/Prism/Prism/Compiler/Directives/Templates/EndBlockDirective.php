<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Templates;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

final class EndBlockDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'endblock';
    }

    public function compile(string $expression): string
    {
        return '<?php $__prism->endBlock(); ?>';
    }
}
