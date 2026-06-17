<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Grammar;

use Horizon\QueryBuilder\Clauses\JoinClause;
use Horizon\QueryBuilder\Clauses\LimitClause;
use Horizon\QueryBuilder\Clauses\OrderByClause;
use Horizon\QueryBuilder\Clauses\WhereClause;

abstract class QueryGrammar
{
    protected string $tablePrefix = '';

    /** SELECT */

    /**
     * @param list<string>        $columns
     * @param list<JoinClause>    $joins
     * @param list<WhereClause>   $wheres
     * @param list<OrderByClause> $orders
     */
    public function compileSelect(
        string $table,
        array $columns,
        array $joins,
        array $wheres,
        array $orders,
        ?LimitClause $limit,
    ): string {
        $parts = [];

        $parts[] = 'SELECT ' . $this->columnize($columns);
        $parts[] = 'FROM ' . $this->wrapTable($table);

        foreach ($joins as $join) {
            $parts[] = $this->compileJoin($join);
        }

        if ($wheres !== []) {
            $parts[] = $this->compileWheres($wheres);
        }

        if ($orders !== []) {
            $parts[] = $this->compileOrderBy($orders);
        }

        if ($limit !== null) {
            $parts[] = $this->compileLimit($limit);
        }

        return implode(' ', $parts);
    }

    /** INSERT */

    /** @param array<string, mixed> $values */
    public function compileInsert(string $table, array $values): string
    {
        $columns = $this->columnize(array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        return "INSERT INTO {$this->wrapTable($table)} ({$columns}) VALUES ({$placeholders})";
    }

    /** UPDATE */

    /**
     * @param array<string, mixed> $values
     * @param list<WhereClause>    $wheres
     */
    public function compileUpdate(string $table, array $values, array $wheres): string
    {
        $set = implode(', ', array_map(
            fn(string $col) => $this->wrap($col) . ' = ?',
            array_keys($values),
        ));

        $sql = "UPDATE {$this->wrapTable($table)} SET {$set}";

        if ($wheres !== []) {
            $sql .= ' ' . $this->compileWheres($wheres);
        }

        return $sql;
    }

   /** DELETE */

    /** @param list<WhereClause> $wheres */
    public function compileDelete(string $table, array $wheres): string
    {
        $sql = "DELETE FROM {$this->wrapTable($table)}";

        if ($wheres !== []) {
            $sql .= ' ' . $this->compileWheres($wheres);
        }

        return $sql;
    }

    /** Helpers */

    protected function compileJoin(JoinClause $join): string
    {
        $conditions = array_map(
            fn($c) => "{$this->wrap($c['first'])} {$c['operator']} {$this->wrap($c['second'])}",
            $join->getConditions(),
        );

        return "{$join->type} JOIN {$this->wrapTable($join->table)} ON " . implode(' AND ', $conditions);
    }

    /** @param list<WhereClause> $wheres */
    protected function compileWheres(array $wheres): string
    {
        $sql = '';

        foreach ($wheres as $i => $where) {
            $prefix = $i === 0 ? 'WHERE ' : " {$where->boolean} ";

            if ($where->isRaw) {
                $sql .= $prefix . (string) $where->column;
            } else {
                $sql .= $prefix . $this->wrap((string) $where->column) . " {$where->operator} ?";
            }
        }

        return $sql;
    }

    /** @param list<OrderByClause> $orders */
    protected function compileOrderBy(array $orders): string
    {
        $parts = array_map(
            fn(OrderByClause $o) => $this->wrap($o->column) . ' ' . strtoupper($o->direction),
            $orders,
        );

        return 'ORDER BY ' . implode(', ', $parts);
    }

    protected function compileLimit(LimitClause $limit): string
    {
        $sql = "LIMIT {$limit->limit}";

        if ($limit->offset > 0) {
            $sql .= " OFFSET {$limit->offset}";
        }

        return $sql;
    }

    /** @param list<string> $columns */
    protected function columnize(array $columns): string
    {
        if ($columns === [] || $columns === ['*']) {
            return '*';
        }

        return implode(', ', array_map(
            fn (string $column): string => str_contains($column, '(') ? $column : $this->wrap($column),
            $columns,
        ));
    }

    protected function wrapTable(string $table): string
    {
        return $this->wrap($this->tablePrefix . $table);
    }

    abstract protected function wrap(string $value): string;
}
