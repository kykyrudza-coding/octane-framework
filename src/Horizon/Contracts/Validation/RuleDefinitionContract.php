<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

interface RuleDefinitionContract
{
    public function name(): string;

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array;

    public function message(): ?string;

    public function withMessage(string $message): static;
}
