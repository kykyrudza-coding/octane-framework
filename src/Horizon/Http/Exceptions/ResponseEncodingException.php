<?php

declare(strict_types=1);

namespace Horizon\Http\Exceptions;

use Throwable;

class ResponseEncodingException extends HttpException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct(500, $message, $previous);
    }
}
