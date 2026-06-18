<?php

declare(strict_types=1);

namespace Horizon\Exception\Renderers;

use Horizon\Arch\Application;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Exception\Renderers\ErrorRendererContract;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class JsonErrorRenderer implements ErrorRendererContract
{
    public function render(Throwable $exception, bool $debug = true): string
    {
        $error = FlattenException::createFromThrowable($exception, $this->statusCode($exception));

        $payload = [
            'error' => [
                'type' => $debug ? $error->getClass() : 'ServerError',
                'message' => $debug ? $error->getMessage() : 'Something went wrong.',
                'status' => $error->getStatusCode(),
            ],
        ];

        if ($debug) {
            $payload['error']['file'] = $error->getFile();
            $payload['error']['line'] = $error->getLine();
            $payload['error']['trace'] = $error->getTrace();
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        if ($this->config('exceptions.rendering.json.pretty', true) === true) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($payload, $flags);
    }

    public function contentType(): string
    {
        return 'application/json';
    }

    protected function statusCode(Throwable $exception): int
    {
        $code = $exception->getCode();

        return $code >= 400 && $code < 600 ? $code : 500;
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
}
