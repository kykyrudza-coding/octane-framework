<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

use Horizon\Contracts\Validation\RuleDefinitionContract;

abstract readonly class AbstractRuleDefinition implements RuleDefinitionContract
{
    public function __construct(
        private ?string $message = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return [];
    }

    public function message(): ?string
    {
        return $this->message;
    }
}
