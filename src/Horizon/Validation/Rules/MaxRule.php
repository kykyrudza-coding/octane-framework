<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class MaxRule extends AbstractRuleDefinition
{
    public function __construct(
        private int|float $max,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'max';
    }

    public function parameters(): array
    {
        return ['max' => $this->max];
    }

    public function withMessage(string $message): static
    {
        return new self($this->max, $message);
    }
}
