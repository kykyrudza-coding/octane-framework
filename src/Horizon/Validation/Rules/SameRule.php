<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class SameRule extends AbstractRuleDefinition
{
    public function __construct(
        private string $field,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'same';
    }

    public function parameters(): array
    {
        return ['field' => $this->field];
    }

    public function withMessage(string $message): static
    {
        return new self($this->field, $message);
    }
}
