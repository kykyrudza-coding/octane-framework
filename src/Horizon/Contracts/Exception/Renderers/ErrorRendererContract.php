<?php

declare(strict_types=1);

namespace Horizon\Contracts\Exception\Renderers;

use Throwable;

interface ErrorRendererContract
{
    public function render(Throwable $exception, bool $debug = true): string;

    public function contentType(): string;
}
