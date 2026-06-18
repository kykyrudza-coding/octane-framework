<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Templates;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

final class SlotDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'slot';
    }

    public function compile(string $expression): string
    {
        $expression = trim($expression, "'\"");

        return "<?php echo \$__prism->slot('{$expression}'); ?>";
    }
}
