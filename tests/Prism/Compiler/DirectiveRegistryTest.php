<?php

declare(strict_types=1);

namespace Tests\Prism\Compiler;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;
use Horizon\Prism\Prism\Compiler\DirectiveRegistry;
use PHPUnit\Framework\TestCase;

class DirectiveRegistryTest extends TestCase
{
    public function test_can_register_and_get_directive(): void
    {
        $registry = new DirectiveRegistry();

        $directive = new class implements DirectiveContract {
            public function name(): string { return 'test'; }
            public function compile(string $expression): string { return '<?php test ?>'; }
        };

        $registry->register($directive);

        $this->assertSame($directive, $registry->get('test'));
    }

    public function test_returns_null_for_unknown_directive(): void
    {
        $registry = new DirectiveRegistry();

        $this->assertNull($registry->get('unknown'));
    }

    public function test_has_returns_true_for_registered_directive(): void
    {
        $registry = new DirectiveRegistry();

        $directive = new class implements DirectiveContract {
            public function name(): string { return 'test'; }
            public function compile(string $expression): string { return ''; }
        };

        $registry->register($directive);

        $this->assertTrue($registry->has('test'));
        $this->assertFalse($registry->has('unknown'));
    }
}
