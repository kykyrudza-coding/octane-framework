<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Grammar;

final class MySqlQueryGrammar extends QueryGrammar
{
    protected function wrap(string $value): string
    {
        if ($value === '*') {
            return '*';
        }

        // Handle table.column notation
        if (str_contains($value, '.')) {
            return implode('.', array_map(
                fn(string $segment) => $segment === '*' ? '*' : '`' . str_replace('`', '``', $segment) . '`',
                explode('.', $value),
            ));
        }

        return '`' . str_replace('`', '``', $value) . '`';
    }
}
