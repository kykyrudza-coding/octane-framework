<?php

declare(strict_types=1);

namespace Horizon\Exception\Renderers;

use Horizon\Contracts\Exception\ErrorRendererContract;
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

        return (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
}
