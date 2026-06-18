<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class MinRule extends AbstractRuleDefinition
{
    public function __construct(
        private int|float $min,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'min';
    }

    public function parameters(): array
    {
        return ['min' => $this->min];
    }

    public function withMessage(string $message): static
    {
        return new self($this->min, $message);
    }
}
