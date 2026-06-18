<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;

final class InvalidCastException extends RuntimeException
{
    public function __construct(string $cast, string $property, string $model)
    {
        parent::__construct(
            "Cast [$cast] for property [$property] on model [$model] must implement CastContract."
        );
    }
}
