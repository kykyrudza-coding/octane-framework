<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class NullableRule extends AbstractRuleDefinition
{
    public function name(): string
    {
        return 'nullable';
    }

    public function withMessage(string $message): static
    {
        return new self($message);
    }
}
