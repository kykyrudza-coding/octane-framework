<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class TypeRule extends AbstractRuleDefinition
{
    public function __construct(
        private string $type,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return $this->type;
    }

    public function parameters(): array
    {
        return ['type' => $this->type];
    }

    public function withMessage(string $message): static
    {
        return new self($this->type, $message);
    }
}
