<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;

final class InvalidRelationTypeException extends RuntimeException
{
    public function __construct(string $type, string $model)
    {
        parent::__construct(
            "Invalid relation type [$type] defined on model [$model]."
        );
    }
}
