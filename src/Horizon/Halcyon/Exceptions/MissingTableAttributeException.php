<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;

final class MissingTableAttributeException extends RuntimeException
{
    public function __construct(string $class)
    {
        parent::__construct(
            "Model [$class] is missing the #[Table] attribute. All models must explicitly define their table name."
        );
    }
}
