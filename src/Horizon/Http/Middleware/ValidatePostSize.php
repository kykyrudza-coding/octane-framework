<?php

declare(strict_types=1);

namespace Horizon\Http\Middleware;

use Closure;
use Horizon\Contracts\Http\Middleware\MiddlewareContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use RuntimeException;

class ValidatePostSize implements MiddlewareContract
{
    public function handle(RequestContextContract $context, Closure $next): ResponseContract
    {
        $max = $this->postMaxSize();

        $contentLength = $context->getRequest()->server('CONTENT_LENGTH', 0);
        $contentLength = is_numeric($contentLength) ? (int) $contentLength : 0;

        if ($max && $contentLength > $max) {
            throw new RuntimeException('Request entity too large.');
        }

        $response = $next($context);
        if (! $response instanceof ResponseContract) {
            throw new RuntimeException('Middleware chain must return a ResponseContract instance.');
        }

        return $response;
    }

    protected function postMaxSize(): ?int
    {
        $postMaxSize = ini_get('post_max_size');
        if ($postMaxSize === false) {
            return null;
        }

        if (is_numeric($postMaxSize)) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));

        $postMaxSize = (int) $postMaxSize;

        return match ($metric) {
            'K' => $postMaxSize * 1024,
            'M' => $postMaxSize * 1024 * 1024,
            'G' => $postMaxSize * 1024 * 1024 * 1024,
            default => $postMaxSize,
        };
    }
}
