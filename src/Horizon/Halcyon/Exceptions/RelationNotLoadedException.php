<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;

final class RelationNotLoadedException extends RuntimeException
{
    public function __construct(string $relation, string $model)
    {
        parent::__construct(
            "Relation '$relation' not loaded on [$model]. Use with('$relation') in your query."
        );
    }
}
