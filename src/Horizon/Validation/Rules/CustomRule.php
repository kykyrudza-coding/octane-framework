<?php

declare(strict_types=1);

namespace Horizon\Validation\Rules;

use Horizon\Contracts\Validation\ValidationRuleContract;

final readonly class CustomRule extends AbstractRuleDefinition
{
    /**
     * @param  class-string<ValidationRuleContract>|ValidationRuleContract  $rule
     */
    public function __construct(
        private string|ValidationRuleContract $rule,
        ?string $message = null,
    ) {
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'custom';
    }

    public function parameters(): array
    {
        return ['rule' => $this->rule];
    }

    public function withMessage(string $message): static
    {
        return new self($this->rule, $message);
    }
}
