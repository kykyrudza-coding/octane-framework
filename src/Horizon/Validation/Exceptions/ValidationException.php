<?php

declare(strict_types=1);

namespace Horizon\Validation\Exceptions;

use Horizon\Contracts\Validation\ValidationErrorBagContract;
use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(
        private readonly ValidationErrorBagContract $errors,
    ) {
        parent::__construct('The given data was invalid.');
    }

    public function errors(): ValidationErrorBagContract
    {
        return $this->errors;
    }
}
