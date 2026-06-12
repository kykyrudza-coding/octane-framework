<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Templates;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;

final class LayoutDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'layout';
    }

    public function compile(string $expression): string
    {
        $expression = trim($expression, "'\"");

        return "<?php \$__prism->layout('$expression'); ?>";
    }
}
