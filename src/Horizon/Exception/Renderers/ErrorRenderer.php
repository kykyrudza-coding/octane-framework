<?php

declare(strict_types=1);

namespace Horizon\Exception\Renderers;

use Horizon\Contracts\Exception\Renderers\ErrorRendererContract;
use Throwable;

class ErrorRenderer implements ErrorRendererContract
{
    public function render(Throwable $exception, bool $debug = true): string
    {
        return $this->renderer()->render($exception, $debug);
    }

    public function contentType(): string
    {
        return $this->renderer()->contentType();
    }

    protected function renderer(): ErrorRendererContract
    {
        if (PHP_SAPI === 'cli') {
            return new ConsoleErrorRenderer;
        }

        if ($this->expectsJson()) {
            return new JsonErrorRenderer;
        }

        return new HtmlErrorRenderer;
    }

    protected function expectsJson(): bool
    {
        try {
            $request = request();
            $accept = $request->server('HTTP_ACCEPT', '');
            $contentType = $request->server('CONTENT_TYPE', '');
            $requestedWith = $request->server('HTTP_X_REQUESTED_WITH', '');

            $accept = is_string($accept) ? strtolower($accept) : '';
            $contentType = is_string($contentType) ? strtolower($contentType) : '';
            $requestedWith = is_string($requestedWith) ? strtolower($requestedWith) : '';

            return str_contains($accept, 'application/json')
                || str_contains($contentType, 'application/json')
                || $requestedWith === 'xmlhttprequest';
        } catch (Throwable) {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            $accept = is_string($accept) ? strtolower($accept) : '';
            $requestedWith = is_string($requestedWith) ? strtolower($requestedWith) : '';
            $contentType = is_string($contentType) ? strtolower($contentType) : '';

            return str_contains($accept, 'application/json')
                || str_contains($contentType, 'application/json')
                || $requestedWith === 'xmlhttprequest';
        }
    }
}
