<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;

final class PropertyNotFoundException extends RuntimeException
{
    public function __construct(string $column, string $model)
    {
        parent::__construct(
            "Column [$column] from database has no corresponding property on model [$model]."
        );
    }
}
