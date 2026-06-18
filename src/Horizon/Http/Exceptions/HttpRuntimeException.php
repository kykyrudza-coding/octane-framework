<?php

declare(strict_types=1);

namespace Horizon\Http\Exceptions;

class HttpRuntimeException extends HttpException
{
    public function __construct(string $message, int $statusCode = 500)
    {
        parent::__construct($statusCode, $message);
    }
}
