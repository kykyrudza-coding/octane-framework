<?php

declare(strict_types=1);

namespace Horizon\Exception\Renderers;

use Horizon\Arch\Application;
use Horizon\Contracts\Exception\Renderers\ErrorRendererContract;
use ReflectionClass;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class HtmlErrorRenderer implements ErrorRendererContract
{
    public function render(Throwable $exception, bool $debug = true): string
    {
        $statusCode = $this->statusCode($exception);
        $error = FlattenException::createFromThrowable($exception, $statusCode);

        $appView = null;
        try {
            $appView = Application::getInstance()->basePath("resources/views/errors/{$statusCode}.php");
        } catch (Throwable) {
        }

        if ($appView && is_file($appView) && (! $debug || $statusCode < 500)) {
            $view = $appView;
        } elseif (! $debug || $statusCode < 500) {
            $frameworkView = __DIR__."/../Views/{$statusCode}.php";
            $view = is_file($frameworkView) ? $frameworkView : __DIR__.'/../Views/500.php';
        } else {
            $view = __DIR__.'/../Views/error.php';
        }

        if (! is_file($view)) {
            return $this->fallback($error, $exception, $debug);
        }

        $displayTitle = $debug ? $this->displayTitle($exception) : $error->getStatusText();
        $displayClass = $error->getClass();
        $message = $debug ? $error->getMessage() : 'Something went wrong.';

        ob_start();

        try {
            require $view;

            return (string) ob_get_clean();
        } catch (Throwable) {
            ob_end_clean();

            return $this->fallback($error, $exception, $debug);
        }
    }

    public function contentType(): string
    {
        return 'text/html';
    }

    protected function fallback(FlattenException $error, Throwable $exception, bool $debug): string
    {
        $title = $debug ? $this->displayTitle($exception) : $error->getStatusText();
        $message = $debug ? $error->getMessage() : 'Something went wrong.';

        return '<h1>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h1>'
            .'<p>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</p>';
    }

    protected function displayTitle(Throwable $exception): string
    {
        if ($exception instanceof FatalError) {
            return $this->fatalErrorTitle($exception);
        }

        $shortName = (new ReflectionClass($exception))->getShortName();

        return (string) preg_replace('/(?<!^)[A-Z]/', ' $0', $shortName);
    }

    protected function fatalErrorTitle(FatalError $exception): string
    {
        return match ($exception->getError()['type'] ?? E_ERROR) {
            E_PARSE => 'Parse Error',
            E_COMPILE_ERROR => 'Compile Error',
            E_CORE_ERROR => 'Core Error',
            default => 'Fatal Error',
        };
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
}
