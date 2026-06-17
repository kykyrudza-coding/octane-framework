<?php

declare(strict_types=1);

namespace Horizon\Database\Migrations;

final class CompositeIndex
{
    private bool $unique = false;

    private function __construct(
        private readonly array $columns,
    ) {}

    public static function on(array $columns): CompositeIndex
    {
        return new CompositeIndex($columns);
    }

    public function unique(): CompositeIndex
    {
        $this->unique = true;

        return $this;
    }

    public function toDefinition(): array
    {
        return [
            'type'    => 'composite_index',
            'columns' => $this->columns,
            'unique'  => $this->unique,
        ];
    }
}
