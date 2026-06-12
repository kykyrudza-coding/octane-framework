<?php

declare(strict_types=1);

namespace Tests\Prism\Component;

use Horizon\Contracts\Prism\Component\ComponentRegistryContract;
use Horizon\Prism\Prism\Component\Component;
use Horizon\Prism\Prism\Component\ComponentResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TestButtonComponent extends Component
{
    public string $text = 'default';

    public function name(): string
    {
        return 'Button';
    }

    public function render(): string
    {
        return "<button>{$this->text}</button>";
    }
}

class ComponentResolverTest extends TestCase
{
    public function test_resolve_resolves_and_injects_props(): void
    {
        $registry = $this->createMock(ComponentRegistryContract::class);
        $component = new TestButtonComponent();

        $registry->method('get')->with('Button')->willReturn($component);

        $resolver = new ComponentResolver($registry);

        $resolved = $resolver->resolve('Button', ['text' => 'Submit']);

        $this->assertSame('<button>Submit</button>', $resolved->render());
    }

    public function test_resolve_throws_exception_if_not_found(): void
    {
        $registry = $this->createMock(ComponentRegistryContract::class);
        $registry->method('get')->with('Unknown')->willReturn(null);

        $resolver = new ComponentResolver($registry);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Component 'Unknown' is not registered.");

        $resolver->resolve('Unknown');
    }
}
