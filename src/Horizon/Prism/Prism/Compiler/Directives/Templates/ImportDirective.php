<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler\Directives\Templates;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;

final class ImportDirective implements DirectiveContract
{
    public function name(): string
    {
        return 'import';
    }

    public function compile(string $expression): string
    {
        return "<?php echo \$__prism->import({$expression}); ?>";
    }
}
