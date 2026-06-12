<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Component;

interface ComponentResolverContract
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function resolve(string $alias, array $props = []): ComponentContract;
}
