<?php

declare(strict_types=1);

namespace Tests\Prism\Component;

use Horizon\Prism\Prism\Component\Component;
use PHPUnit\Framework\TestCase;

class TestComponent extends Component
{
    public string $text = 'default';
    public string $variant = 'primary';

    public function name(): string
    {
        return 'Test';
    }

    public function render(): string
    {
        return "<button class=\"{$this->variant}\">{$this->text}{$this->slot}</button>";
    }
}

class ComponentTest extends TestCase
{
    public function test_with_props_updates_properties(): void
    {
        $component = new TestComponent();
        
        $updated = $component->withProps([
            'text' => 'Click Me',
            'variant' => 'danger'
        ]);

        $this->assertNotSame($component, $updated);
        $this->assertSame('Click Me', $updated->text);
        $this->assertSame('danger', $updated->variant);
    }

    public function test_with_slot_updates_slot(): void
    {
        $component = new TestComponent();
        
        $updated = $component->withSlot(' Slot Content');

        $this->assertNotSame($component, $updated);
        $this->assertSame('<button class="primary">default Slot Content</button>', $updated->render());
    }

    public function test_ignores_non_existent_props(): void
    {
        $component = new TestComponent();
        
        $updated = $component->withProps([
            'unknown' => 'value'
        ]);

        $this->assertSame('default', $updated->text);
    }
}
