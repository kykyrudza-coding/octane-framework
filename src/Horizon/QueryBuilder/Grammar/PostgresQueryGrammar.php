<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Grammar;

final class PostgresQueryGrammar extends QueryGrammar
{
    protected function wrap(string $value): string
    {
        if ($value === '*') {
            return '*';
        }

        if (str_contains($value, '.')) {
            return implode('.', array_map(
                fn(string $s) => $s === '*' ? '*' : '"' . str_replace('"', '""', $s) . '"',
                explode('.', $value),
            ));
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }
}
