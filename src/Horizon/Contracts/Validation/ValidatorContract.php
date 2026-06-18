<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

use Horizon\Validation\Exceptions\ValidationException;

interface ValidatorContract
{
    public function passes(): bool;

    public function fails(): bool;

    public function errors(): ValidationErrorBagContract;

    public function validated(): ValidatedDataContract;

    /**
     * @throws ValidationException
     */
    public function validate(): ValidatedDataContract;
}
