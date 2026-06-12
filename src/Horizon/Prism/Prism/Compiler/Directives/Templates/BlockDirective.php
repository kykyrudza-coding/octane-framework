<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Templates;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;

final class BlockDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'block';
    }

    public function compile(string $expression): string
    {
        $expression = trim($expression, "'\"");

        return "<?php \$__prism->startBlock('{$expression}'); ?>";
    }
}
