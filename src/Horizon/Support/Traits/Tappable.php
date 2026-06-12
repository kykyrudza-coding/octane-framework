<?php

declare(strict_types=1);

namespace Horizon\Support\Traits;

class Tappable
{
    public function tap(callable $callback): static
    {
        $callback($this);
        return $this;
    }
}
