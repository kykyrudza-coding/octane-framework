<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class BetweenRule extends AbstractRuleDefinition
{
    public function __construct(
        private int|float $min,
        private int|float $max,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'between';
    }

    public function parameters(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
        ];
    }

    public function withMessage(string $message): static
    {
        return new self($this->min, $this->max, $message);
    }
}
