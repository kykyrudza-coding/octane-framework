<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Component;

use Horizon\Contracts\Prism\Component\ComponentContract;
use Horizon\Contracts\Prism\Component\ComponentRegistryContract;
use Horizon\Contracts\Prism\Component\ComponentResolverContract;
use RuntimeException;

final readonly class ComponentResolver implements ComponentResolverContract
{
    public function __construct(
        private ComponentRegistryContract $registry,
    ) {}

    public function resolve(string $alias, array $props = []): ComponentContract
    {
        $component = $this->registry->get($alias);

        if ($component === null) {
            throw new RuntimeException(
                "Component '$alias' is not registered. Did you forget to register it in the service provider?"
            );
        }

        // If the component supports props injection, pass them
        if (method_exists($component, 'withProps')) {
            return $component->withProps($props);
        }

        return $component;
    }
}
