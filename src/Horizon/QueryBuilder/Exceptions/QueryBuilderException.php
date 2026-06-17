<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Exceptions;

use RuntimeException;

class QueryBuilderException extends RuntimeException
{
    public static function missingTable(): self
    {
        return new self('No table or model has been specified for the query.');
    }

    public static function unsupportedOperator(string $operator): self
    {
        return new self("Unsupported query operator: [$operator].");
    }

    public static function noConnectionResolved(): self
    {
        return new self('QueryBuilder could not resolve a database connection.');
    }
}
