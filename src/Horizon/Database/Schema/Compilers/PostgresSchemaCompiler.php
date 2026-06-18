<?php

declare(strict_types=1);

namespace Horizon\Database\Schema\Compilers;

use Horizon\Contracts\Database\Schema\Compilers\SchemaCompilerContract;
use Horizon\Database\Exceptions\SchemaException;

final class PostgresSchemaCompiler implements SchemaCompilerContract
{
    public function compileCreate(string $table, array $columns): string
    {
        $definitions = [];
        $constraints = [];
        $indexes     = [];

        foreach ($columns as $column) {
            $definition = $column->toDefinition();

            if ($definition['type'] === 'timestamps') {
                $definitions[] = '"created_at" TIMESTAMP NULL DEFAULT NULL';
                $definitions[] = '"updated_at" TIMESTAMP NULL DEFAULT NULL';
                continue;
            }

            if ($definition['type'] === 'softdeletes') {
                $definitions[] = '"deleted_at" TIMESTAMP NULL DEFAULT NULL';
                continue;
            }

            if ($definition['type'] === 'composite_index') {
                $unique    = $definition['unique'] ? 'UNIQUE ' : '';
                $cols      = implode('", "', $definition['columns']);
                $indexes[] = "{$unique}INDEX (\"{$cols}\")";
                continue;
            }

            $definitions[] = $this->compileColumn($definition);

            if ($definition['references'] && $definition['on']) {
                $constraints[] = sprintf(
                    'FOREIGN KEY ("%s") REFERENCES "%s" ("%s") ON DELETE %s ON UPDATE %s',
                    $definition['name'],
                    $definition['on'],
                    $definition['references'],
                    $definition['onDelete'],
                    $definition['onUpdate'],
                );
            }
        }

        $all = array_merge($definitions, $constraints, $indexes);

        return sprintf(
            "CREATE TABLE \"%s\" (\n  %s\n)",
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
                $clauses[] = "DROP COLUMN \"{$definition['name']}\"";
                continue;
            }

            $clauses[] = 'ADD COLUMN '.$this->compileColumn($definition);
        }

        return sprintf(
            'ALTER TABLE "%s" %s',
            $table,
            implode(', ', $clauses),
        );
    }

    public function compileDrop(string $table): string
    {
        return "DROP TABLE \"{$table}\"";
    }

    public function compileDropIfExists(string $table): string
    {
        return "DROP TABLE IF EXISTS \"{$table}\"";
    }

    public function compileRename(string $from, string $to): string
    {
        return "ALTER TABLE \"{$from}\" RENAME TO \"{$to}\"";
    }

    private function compileColumn(array $definition): string
    {
        $type = $this->compileType($definition);
        $sql  = "\"{$definition['name']}\" {$type}";

        if ($definition['primaryKey']) {
            $sql .= ' PRIMARY KEY';
        }

        $sql .= $definition['nullable'] ? ' NULL' : ' NOT NULL';

        if ($definition['unique']) {
            $sql .= ' UNIQUE';
        }

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
            'longtext'    => 'TEXT',
            'integer'     => $definition['autoIncrement'] ? 'SERIAL' : 'INTEGER',
            'biginteger'  => $definition['autoIncrement'] ? 'BIGSERIAL' : 'BIGINT',
            'tinyinteger' => 'SMALLINT',
            'float'       => 'REAL',
            'decimal'     => 'DECIMAL('.($definition['length'] ?? 8).', 2)',
            'boolean'     => 'BOOLEAN',
            'json'        => 'JSONB',
            'date'        => 'DATE',
            'timestamp'   => 'TIMESTAMP',
            default       => throw new SchemaException(
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
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'{$value}'";
    }
}
