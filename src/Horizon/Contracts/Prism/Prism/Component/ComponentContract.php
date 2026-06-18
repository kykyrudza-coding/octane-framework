<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Prism\Component;

interface ComponentContract
{
    public function render(): string;
}
