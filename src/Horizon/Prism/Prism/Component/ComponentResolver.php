<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Component;

use Horizon\Contracts\Prism\Prism\Component\ComponentContract;
use Horizon\Contracts\Prism\Prism\Component\ComponentRegistryContract;
use Horizon\Contracts\Prism\Prism\Component\ComponentResolverContract;
use Horizon\Prism\Exceptions\ComponentResolutionException;

final readonly class ComponentResolver implements ComponentResolverContract
{
    public function __construct(
        private ComponentRegistryContract $registry,
    ) {}

    public function resolve(string $alias, array $props = []): ComponentContract
    {
        $component = $this->registry->get($alias);

        if ($component === null) {
            throw new ComponentResolutionException(
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
