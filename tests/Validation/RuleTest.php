<?php

declare(strict_types=1);

namespace Tests\Validation;

use Horizon\Contracts\Validation\ValidationRuleContract;
use Horizon\Validation\Rule;
use Horizon\Validation\Rules\CustomRule;
use Horizon\Validation\Rules\ExistsRule;
use Horizon\Validation\Rules\MinRule;
use Horizon\Validation\Rules\RequiredRule;
use Horizon\Validation\Rules\TypeRule;
use PHPUnit\Framework\TestCase;

final class RuleTest extends TestCase
{
    public function test_rule_builds_fluent_rule_set(): void
    {
        $rules = Rule::required()
            ->string()
            ->min(3)
            ->max(255);

        $this->assertSame(['required', 'string', 'min', 'max'], $rules->names());
        $this->assertInstanceOf(RequiredRule::class, $rules->rules()[0]);
        $this->assertInstanceOf(TypeRule::class, $rules->rules()[1]);
        $this->assertInstanceOf(MinRule::class, $rules->rules()[2]);
        $this->assertSame(['min' => 3], $rules->rules()[2]->parameters());
        $this->assertSame(['max' => 255], $rules->rules()[3]->parameters());
    }

    public function test_rule_set_is_immutable(): void
    {
        $base = Rule::required();
        $extended = $base->email();

        $this->assertSame(['required'], $base->names());
        $this->assertSame(['required', 'email'], $extended->names());
    }

    public function test_exists_rule_keeps_database_target_metadata(): void
    {
        $rules = Rule::exists('users', 'email');

        $this->assertInstanceOf(ExistsRule::class, $rules->rules()[0]);
        $this->assertSame([
            'table' => 'users',
            'column' => 'email',
        ], $rules->rules()[0]->parameters());
    }

    public function test_custom_rule_definition_keeps_rule_class_or_instance(): void
    {
        $classRule = Rule::rule(CustomPasswordRule::class)->rules()[0];
        $instanceRule = Rule::rule(new CustomPasswordRule)->rules()[0];

        $this->assertInstanceOf(CustomRule::class, $classRule);
        $this->assertSame(CustomPasswordRule::class, $classRule->parameters()['rule']);
        $this->assertInstanceOf(CustomPasswordRule::class, $instanceRule->parameters()['rule']);
    }

    public function test_rule_definition_can_carry_custom_message(): void
    {
        $rule = (new MinRule(8))->withMessage('Password is too short.');

        $this->assertSame('min', $rule->name());
        $this->assertSame(['min' => 8], $rule->parameters());
        $this->assertSame('Password is too short.', $rule->message());
    }
}

final class CustomPasswordRule implements ValidationRuleContract
{
    public function passes(string $attribute, mixed $value, array $data = []): bool
    {
        return is_string($value) && strlen($value) >= 8;
    }

    public function message(string $attribute): string
    {
        return "The [$attribute] field is not strong enough.";
    }
}
