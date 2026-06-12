<?php

declare(strict_types=1);

namespace Horizon\Http\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /**
     * @var array<string, scalar|null>
     */
    private array $headers;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function __construct(int $statusCode, string $message = '', ?Throwable $previous = null, array $headers = [])
    {
        parent::__construct($message, $statusCode, $previous);
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    /**
     * @return array<string, scalar|null>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
