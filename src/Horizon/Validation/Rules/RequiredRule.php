<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class RequiredRule extends AbstractRuleDefinition
{
    public function name(): string
    {
        return 'required';
    }

    public function withMessage(string $message): static
    {
        return new self($message);
    }
}
