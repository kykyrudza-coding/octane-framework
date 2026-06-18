<?php

declare(strict_types=1);

namespace Horizon\Exception;

use Horizon\Arch\Application;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Exception\HandlerContract;
use Horizon\Contracts\Exception\Renderers\ErrorRendererContract;
use Symfony\Component\ErrorHandler\ErrorHandler as SymfonyErrorHandler;
use Throwable;

class Handler implements HandlerContract
{
    public function __construct(
        protected ErrorRendererContract $renderer,
        protected ?bool $debug = null,
    ) {}

    protected function isDebugMode(): bool
    {
        if ($this->debug !== null) {
            return $this->debug;
        }

        $debug = $this->config('exceptions.debug', $this->config('app.debug', env('APP_DEBUG', false)));

        return (bool) $debug;
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
        foreach ($this->ignoredExceptions() as $ignored) {
            if ($exception instanceof $ignored) {
                return;
            }
        }

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

    /**
     * @return list<class-string<Throwable>>
     */
    private function ignoredExceptions(): array
    {
        $ignored = $this->config('exceptions.reporting.ignore', []);

        if (! is_array($ignored)) {
            return [];
        }

        return array_values(array_filter(
            $ignored,
            static fn (mixed $class): bool => is_string($class) && is_subclass_of($class, Throwable::class),
        ));
    }
}
