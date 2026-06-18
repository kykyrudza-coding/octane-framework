<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;
use Throwable;

final class HydrationException extends RuntimeException
{
    public function __construct(string $model, string $property, Throwable $previous)
    {
        parent::__construct(
            "Failed to hydrate property [$property] on model [$model]: {$previous->getMessage()}",
            previous: $previous
        );
    }
}
