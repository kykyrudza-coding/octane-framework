<?php

declare(strict_types=1);

namespace Horizon\Database\Schema\Compilers;

use Horizon\Contracts\Database\Schema\SchemaCompilerContract;
use InvalidArgumentException;

final class MySqlSchemaCompiler implements SchemaCompilerContract
{
    public function compileCreate(string $table, array $columns): string
    {
        $definitions = [];
        $indexes     = [];

        foreach ($columns as $column) {
            $definition = $column->toDefinition();

            if ($definition['type'] === 'timestamps') {
                $definitions[] = '`created_at` TIMESTAMP NULL DEFAULT NULL';
                $definitions[] = '`updated_at` TIMESTAMP NULL DEFAULT NULL';
                continue;
            }

            if ($definition['type'] === 'softdeletes') {
                $definitions[] = '`deleted_at` TIMESTAMP NULL DEFAULT NULL';
                continue;
            }

            if ($definition['type'] === 'composite_index') {
                $cols      = array_map(fn (string $c): string => "`{$c}`", $definition['columns']);
                $unique    = $definition['unique'] ? 'UNIQUE ' : '';
                $indexes[] = "{$unique}INDEX (`".implode('`, `', $definition['columns']).'`)';
                continue;
            }

            $definitions[] = $this->compileColumn($definition);

            if ($definition['unique']) {
                $indexes[] = "UNIQUE INDEX (`{$definition['name']}`)";
            } elseif ($definition['index']) {
                $indexes[] = "INDEX (`{$definition['name']}`)";
            }

            if ($definition['references'] && $definition['on']) {
                $definitions[] = sprintf(
                    'FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s',
                    $definition['name'],
                    $definition['on'],
                    $definition['references'],
                    $definition['onDelete'],
                    $definition['onUpdate'],
                );
            }
        }

        $all = array_merge($definitions, $indexes);

        return sprintf(
            "CREATE TABLE `%s` (\n  %s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            $table,
            implode(",\n  ", $all),
        );
    }

    public function compileAlter(string $table, array $columns): string
    {
        $clauses = [];

        foreach ($columns as $column) {
            $definition = $column->toDefinition();

            if ($definition['type'] === 'drop') {
                $clauses[] = "DROP COLUMN `{$definition['name']}`";
                continue;
            }

            $compiled  = $this->compileColumn($definition);
            $after     = $definition['after'] ? " AFTER `{$definition['after']}`" : '';
            $clauses[] = "ADD COLUMN {$compiled}{$after}";
        }

        return sprintf(
            'ALTER TABLE `%s` %s',
            $table,
            implode(', ', $clauses),
        );
    }

    public function compileDrop(string $table): string
    {
        return "DROP TABLE `{$table}`";
    }

    public function compileDropIfExists(string $table): string
    {
        return "DROP TABLE IF EXISTS `{$table}`";
    }

    public function compileRename(string $from, string $to): string
    {
        return "RENAME TABLE `{$from}` TO `{$to}`";
    }

    private function compileColumn(array $definition): string
    {
        $sql = "`{$definition['name']}` ".$this->compileType($definition);

        if ($definition['unsigned']) {
            $sql .= ' UNSIGNED';
        }

        if ($definition['autoIncrement']) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($definition['primaryKey']) {
            $sql .= ' PRIMARY KEY';
        }

        $sql .= $definition['nullable'] ? ' NULL' : ' NOT NULL';

        if ($definition['hasDefault']) {
            $sql .= ' DEFAULT '.$this->compileDefault($definition['default']);
        }

        return $sql;
    }

    private function compileType(array $definition): string
    {
        return match ($definition['type']) {
            'string'      => 'VARCHAR('.($definition['length'] ?? 255).')',
            'text'        => 'TEXT',
            'longtext'    => 'LONGTEXT',
            'integer'     => 'INT',
            'biginteger'  => 'BIGINT',
            'tinyinteger' => 'TINYINT',
            'float'       => 'FLOAT',
            'decimal'     => 'DECIMAL('.($definition['length'] ?? 8).', 2)',
            'boolean'     => 'TINYINT(1)',
            'json'        => 'JSON',
            'date'        => 'DATE',
            'timestamp'   => 'TIMESTAMP',
            default       => throw new InvalidArgumentException(
                "Unknown column type [{$definition['type']}].",
            ),
        };
    }

    private function compileDefault(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'{$value}'";
    }
}
