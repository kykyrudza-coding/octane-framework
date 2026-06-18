<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Component;

use Horizon\Contracts\Prism\Prism\Component\ComponentContract;

/**
 * Abstract base class for Prism components.
 *
 * Usage:
 *   class ButtonComponent extends Component
 *   {
 *       public function __construct(
 *           private string $text = '',
 *           private string $variant = 'primary',
 *       ) {}
 *
 *       public function name(): string { return 'Button'; }
 *
 *       public function render(): string
 *       {
 *           return "<button class=\"btn btn-{$this->variant}\">{$this->text}</button>";
 *       }
 *   }
 */
abstract class Component implements ComponentContract
{
    /** @var array<string, mixed> */
    protected array $props = [];

    protected string $slot = '';

    /**
     * Returns the component's registered alias name.
     */
    abstract public function name(): string;

    /**
     * Returns a new instance of the component with the given props injected.
     *
     * @param  array<string, mixed>  $props
     */
    public function withProps(array $props): static
    {
        $clone = clone $this;
        $clone->props = $props;

        // Attempt to set public/protected properties by matching prop keys
        foreach ($props as $key => $value) {
            if (property_exists($clone, $key)) {
                $clone->{$key} = $value;
            }
        }

        return $clone;
    }

    public function withSlot(string $slot): static
    {
        $clone = clone $this;
        $clone->slot = $slot;

        return $clone;
    }

    abstract public function render(): string;
}
