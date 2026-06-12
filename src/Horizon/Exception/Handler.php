<?php

declare(strict_types=1);

namespace Horizon\Exception;

use Horizon\Contracts\Exception\ErrorRendererContract;
use Horizon\Contracts\Exception\ExceptionHandlerContract;
use Symfony\Component\ErrorHandler\ErrorHandler as SymfonyErrorHandler;
use Throwable;

class Handler implements ExceptionHandlerContract
{
    public function __construct(
        protected ErrorRendererContract $renderer,
        protected ?bool $debug = null,
    ) {}

    protected function isDebugMode(): bool
    {
        return $this->debug ?? (bool) env('APP_DEBUG', false);
    }

    public function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        $handler = SymfonyErrorHandler::register();
        $handler->setExceptionHandler(fn (Throwable $exception) => $this->handle($exception));
    }

    public function report(Throwable $exception): void
    {
        error_log((string) $exception);
    }

    public function render(Throwable $exception): string
    {
        return $this->renderer->render($exception, $this->isDebugMode());
    }

    public function handle(Throwable $exception): void
    {
        $this->send($exception);
        exit(1);
    }

    public function send(Throwable $exception): void
    {
        $this->report($exception);

        $this->clearOutputBuffers();

        if (PHP_SAPI !== 'cli' && ! headers_sent()) {
            http_response_code($this->statusCode($exception));
            header('Content-Type: '.$this->contentType().'; charset=UTF-8');
        }

        echo $this->render($exception);
        flush();
    }

    protected function statusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();

            return is_int($statusCode) ? $statusCode : 500;
        }

        $code = $exception->getCode();

        return is_int($code) && $code >= 400 && $code < 600 ? $code : 500;
    }

    protected function contentType(): string
    {
        return $this->renderer->contentType();
    }

    protected function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}
