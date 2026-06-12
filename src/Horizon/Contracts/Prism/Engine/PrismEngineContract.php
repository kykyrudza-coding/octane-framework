<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism\Engine;

interface PrismEngineContract
{
    public function render(string $compiledPath, array $data = []): string;
}
