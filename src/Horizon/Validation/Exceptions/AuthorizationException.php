<?php

declare(strict_types=1);

namespace Horizon\Validation\Exceptions;

use RuntimeException;

final class AuthorizationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This action is unauthorized.');
    }
}
