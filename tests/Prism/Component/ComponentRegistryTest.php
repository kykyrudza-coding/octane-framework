<?php

declare(strict_types=1);

namespace Tests\Prism\Component;

use Horizon\Contracts\Prism\Prism\Component\ComponentContract;
use Horizon\Prism\Prism\Component\ComponentRegistry;
use PHPUnit\Framework\TestCase;

class ComponentRegistryTest extends TestCase
{
    public function test_can_register_and_get_component(): void
    {
        $registry = new ComponentRegistry();
        
        $component = new class implements ComponentContract {
            public function name(): string { return 'button'; }
            public function render(): string { return ''; }
        };

        $registry->register($component);

        $this->assertTrue($registry->has('button'));
        $this->assertSame($component, $registry->get('button'));
    }

    public function test_returns_null_for_unknown_component(): void
    {
        $registry = new ComponentRegistry();

        $this->assertFalse($registry->has('unknown'));
        $this->assertNull($registry->get('unknown'));
    }
}
