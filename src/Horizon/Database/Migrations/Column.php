<?php

declare(strict_types=1);

namespace Horizon\Database\Migrations;

use Horizon\Contracts\Database\Migrations\ColumnContract;

final class Column implements ColumnContract
{
    private string $type;
    private string $name;
    private bool $nullable = false;
    private bool $unsigned = false;
    private bool $unique = false;
    private bool $index = false;
    private bool $autoIncrement = false;
    private bool $primaryKey = false;
    private mixed $default = null;
    private bool $hasDefault = false;
    private ?int $length = null;
    private ?string $after = null;
    private ?string $references = null;
    private ?string $on = null;
    private string $onDelete = 'RESTRICT';
    private string $onUpdate = 'RESTRICT';

    private function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public static function id(): Column
    {
        return Column::primaryKey('id')->autoIncrement()->unsigned();
    }

    public static function primaryKey(string $name): Column
    {
        $column = new Column('integer', $name);
        $column->primaryKey = true;

        return $column;
    }

    public static function string(string $name, int $length = 255): Column
    {
        $column = new Column('string', $name);
        $column->length = $length;

        return $column;
    }

    public static function email(string $name = 'email'): Column
    {
        return Column::string($name, 255);
    }

    public static function text(string $name): Column
    {
        return new Column('text', $name);
    }

    public static function longText(string $name): Column
    {
        return new Column('longtext', $name);
    }

    public static function integer(string $name): Column
    {
        return new Column('integer', $name);
    }

    public static function bigInteger(string $name): Column
    {
        return new Column('biginteger', $name);
    }

    public static function tinyInteger(string $name): Column
    {
        return new Column('tinyinteger', $name);
    }

    public static function float(string $name): Column
    {
        return new Column('float', $name);
    }

    public static function decimal(string $name, int $precision = 8, int $scale = 2): Column
    {
        $column = new Column('decimal', $name);
        $column->length = $precision;

        return $column;
    }

    public static function boolean(string $name): Column
    {
        return new Column('boolean', $name);
    }

    public static function json(string $name): Column
    {
        return new Column('json', $name);
    }

    public static function date(string $name): Column
    {
        return new Column('date', $name);
    }

    public static function timestamp(string $name): Column
    {
        return new Column('timestamp', $name);
    }

    public static function timestamps(): Column
    {
        return new Column('timestamps', '');
    }

    public static function softDeletes(): Column
    {
        return new Column('softdeletes', '');
    }

    public static function foreignId(string $name): Column
    {
        $column = new Column('biginteger', $name);
        $column->unsigned = true;

        return $column;
    }

    public function nullable(): Column
    {
        $this->nullable = true;

        return $this;
    }

    public function notNull(): Column
    {
        $this->nullable = false;

        return $this;
    }

    public function default(mixed $value): Column
    {
        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }

    public function unique(): Column
    {
        $this->unique = true;

        return $this;
    }

    public function unsigned(): Column
    {
        $this->unsigned = true;

        return $this;
    }

    public function index(): Column
    {
        $this->index = true;

        return $this;
    }

    public function autoIncrement(): Column
    {
        $this->autoIncrement = true;

        return $this;
    }

    public function after(string $column): Column
    {
        $this->after = $column;

        return $this;
    }

    public function references(string $column): Column
    {
        $this->references = $column;

        return $this;
    }

    public function on(string $table): Column
    {
        $this->on = $table;

        return $this;
    }

    public function cascadeOnDelete(): Column
    {
        $this->onDelete = 'CASCADE';

        return $this;
    }

    public function nullOnDelete(): Column
    {
        $this->onDelete = 'SET NULL';

        return $this;
    }

    public function cascadeOnUpdate(): Column
    {
        $this->onUpdate = 'CASCADE';

        return $this;
    }

    public function toDefinition(): array
    {
        return [
            'type'          => $this->type,
            'name'          => $this->name,
            'nullable'      => $this->nullable,
            'unsigned'      => $this->unsigned,
            'unique'        => $this->unique,
            'index'         => $this->index,
            'autoIncrement' => $this->autoIncrement,
            'primaryKey'    => $this->primaryKey,
            'default'       => $this->hasDefault ? $this->default : null,
            'hasDefault'    => $this->hasDefault,
            'length'        => $this->length,
            'after'         => $this->after,
            'references'    => $this->references,
            'on'            => $this->on,
            'onDelete'      => $this->onDelete,
            'onUpdate'      => $this->onUpdate,
        ];
    }
}
