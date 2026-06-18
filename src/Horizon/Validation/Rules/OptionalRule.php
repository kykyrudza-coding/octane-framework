<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

final readonly class OptionalRule extends AbstractRuleDefinition
{
    public function name(): string
    {
        return 'optional';
    }

    public function withMessage(string $message): static
    {
        return new self($message);
    }
}
