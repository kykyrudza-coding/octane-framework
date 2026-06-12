<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;
use Horizon\Contracts\Prism\Compiler\DirectiveRegistryContract;
use InvalidArgumentException;

class DirectiveRegistry implements DirectiveRegistryContract
{
    /**
     * @var array <string, DirectiveContract>
     */
    private array $directives = [];

    public function register(callable|DirectiveContract $directive, ?string $name = null): void
    {
        if ($directive instanceof DirectiveContract) {
            $this->directives[$directive->name()] = $directive;
            return;
        }

        if ($name === null) {
            throw new InvalidArgumentException(
                'Directive name cannot be null when registering a callable.'
            );
        }

        $this->directives[$name] = $directive;
    }

    public function get(string $name): ?DirectiveContract
    {
        return $this->directives[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->directives[$name]);
    }

    public function all(): array
    {
        return $this->directives;
    }
}
