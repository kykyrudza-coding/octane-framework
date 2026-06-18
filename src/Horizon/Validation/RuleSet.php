<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\Validation\RuleDefinitionContract;
use Horizon\Contracts\Validation\RuleSetContract;
use Horizon\Contracts\Validation\ValidationRuleContract;
use Horizon\Validation\Rules\BetweenRule;
use Horizon\Validation\Rules\CustomRule;
use Horizon\Validation\Rules\EmailRule;
use Horizon\Validation\Rules\ExistsRule;
use Horizon\Validation\Rules\MaxRule;
use Horizon\Validation\Rules\MinRule;
use Horizon\Validation\Rules\NullableRule;
use Horizon\Validation\Rules\OptionalRule;
use Horizon\Validation\Rules\RequiredRule;
use Horizon\Validation\Rules\SameRule;
use Horizon\Validation\Rules\TypeRule;

final readonly class RuleSet implements RuleSetContract
{
    /**
     * @param  list<RuleDefinitionContract>  $rules
     */
    public function __construct(
        private array $rules = [],
    ) {}

    public function add(RuleDefinitionContract $rule): static
    {
        $rules = $this->rules;
        $rules[] = $rule;

        return new self($rules);
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function names(): array
    {
        return array_map(
            static fn (RuleDefinitionContract $rule): string => $rule->name(),
            $this->rules,
        );
    }

    public function required(): static
    {
        return $this->add(new RequiredRule);
    }

    public function nullable(): static
    {
        return $this->add(new NullableRule);
    }

    public function optional(): static
    {
        return $this->add(new OptionalRule);
    }

    public function string(): static
    {
        return $this->add(new TypeRule('string'));
    }

    public function integer(): static
    {
        return $this->add(new TypeRule('integer'));
    }

    public function numeric(): static
    {
        return $this->add(new TypeRule('numeric'));
    }

    public function boolean(): static
    {
        return $this->add(new TypeRule('boolean'));
    }

    public function array(): static
    {
        return $this->add(new TypeRule('array'));
    }

    public function email(): static
    {
        return $this->add(new EmailRule);
    }

    public function min(int|float $min): static
    {
        return $this->add(new MinRule($min));
    }

    public function max(int|float $max): static
    {
        return $this->add(new MaxRule($max));
    }

    public function between(int|float $min, int|float $max): static
    {
        return $this->add(new BetweenRule($min, $max));
    }

    public function same(string $field): static
    {
        return $this->add(new SameRule($field));
    }

    public function exists(string $table, ?string $column = null): static
    {
        return $this->add(new ExistsRule($table, $column));
    }

    /**
     * @param  class-string<ValidationRuleContract>|ValidationRuleContract  $rule
     */
    public function rule(string|ValidationRuleContract $rule): static
    {
        return $this->add(new CustomRule($rule));
    }
}
