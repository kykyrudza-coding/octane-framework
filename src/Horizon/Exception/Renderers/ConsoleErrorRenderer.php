<?php

declare(strict_types=1);

namespace Horizon\Exception\Renderers;

use Horizon\Contracts\Exception\ErrorRendererContract;
use Throwable;

class ConsoleErrorRenderer implements ErrorRendererContract
{
    public function render(Throwable $exception, bool $debug = true): string
    {
        $output = $exception::class.': '.$exception->getMessage().PHP_EOL;

        if (! $debug) {
            return $output;
        }

        $output .= 'in '.$exception->getFile().':'.$exception->getLine().PHP_EOL.PHP_EOL;
        $output .= $exception->getTraceAsString().PHP_EOL;

        return $output;
    }

    public function contentType(): string
    {
        return 'text/plain';
    }
}
