<?php

declare(strict_types=1);

namespace Horizon\Exception\Renderers;

use Horizon\Arch\Application;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
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
        $renderer = $this->config('exceptions.rendering.default', 'auto');

        if ($renderer === 'json') {
            return new JsonErrorRenderer;
        }

        if ($renderer === 'html') {
            return new HtmlErrorRenderer;
        }

        if ($renderer === 'console') {
            return new ConsoleErrorRenderer;
        }

        if (PHP_SAPI === 'cli') {
            return new ConsoleErrorRenderer;
        }

        if ($this->expectsJson()) {
            return new JsonErrorRenderer;
        }

        return new HtmlErrorRenderer;
    }

    private function config(string $key, mixed $default = null): mixed
    {
        try {
            $config = Application::getInstance()->make(ConfigRepositoryContract::class);

            if ($config instanceof ConfigRepositoryContract) {
                return $config->get($key, $default);
            }
        } catch (Throwable) {
            //
        }

        return $default;
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
