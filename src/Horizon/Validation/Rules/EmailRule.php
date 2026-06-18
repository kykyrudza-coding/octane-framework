<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class EmailRule extends AbstractRuleDefinition
{
    public function name(): string
    {
        return 'email';
    }

    public function withMessage(string $message): static
    {
        return new self($message);
    }
}
