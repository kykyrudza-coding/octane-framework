<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class ExistsRule extends AbstractRuleDefinition
{
    public function __construct(
        private string $table,
        private ?string $column = null,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'exists';
    }

    public function parameters(): array
    {
        return [
            'table' => $this->table,
            'column' => $this->column,
        ];
    }

    public function withMessage(string $message): static
    {
        return new self($this->table, $this->column, $message);
    }
}
