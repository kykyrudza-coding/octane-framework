<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\Validation\PresenceVerifierContract;
use Horizon\Contracts\Validation\RuleDefinitionContract;
use Horizon\Contracts\Validation\RuleSetContract;
use Horizon\Contracts\Validation\ValidatedDataContract;
use Horizon\Contracts\Validation\ValidationErrorBagContract;
use Horizon\Contracts\Validation\ValidationRuleContract;
use Horizon\Contracts\Validation\ValidatorContract;
use Horizon\Validation\Exceptions\ValidationException;
use Horizon\Validation\Presence\NullPresenceVerifier;
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

final class Validator implements ValidatorContract
{
    private ValidationErrorBag $errors;

    private ?ValidatedData $validated = null;

    private bool $evaluated = false;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly ?PresenceVerifierContract $presenceVerifier = null,
        private readonly ?DtoFactoryContract $dtoFactory = null,
        private readonly bool $stopOnFirstFailure = false,
        private readonly array $messages = [],
        private readonly array $attributes = [],
    ) {
        $this->errors = new ValidationErrorBag;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function passes(): bool
    {
        $this->evaluate();

        return $this->errors->isEmpty();
    }

    public function fails(): bool
    {
        return ! $this->passes();
    }

    public function errors(): ValidationErrorBagContract
    {
        $this->evaluate();

        return $this->errors;
    }

    public function validated(): ValidatedDataContract
    {
        return $this->validate();
    }

    public function validate(): ValidatedDataContract
    {
        $this->evaluate();

        if ($this->errors->isNotEmpty()) {
            throw new ValidationException($this->errors);
        }

        return $this->validated ?? new ValidatedData([], $this->dtoFactory);
    }

    private function evaluate(): void
    {
        if ($this->evaluated) {
            return;
        }

        $validated = [];

        foreach ($this->rules as $attribute => $rules) {
            $attribute = (string) $attribute;
            $definitions = $this->normalizeRules($rules);
            $present = $this->hasValue($attribute);
            $value = $this->value($attribute);
            $required = $this->firstRule($definitions, RequiredRule::class);

            if (! $present && $this->hasRule($definitions, OptionalRule::class)) {
                continue;
            }

            if ($required !== null && (! $present || $this->empty($value))) {
                if ($this->fail($validated, $required, $attribute)) {
                    return;
                }

                continue;
            }

            if (! $present) {
                continue;
            }

            if ($present && $value === null && $this->hasRule($definitions, NullableRule::class)) {
                $this->setValidatedValue($validated, $attribute, null);

                continue;
            }

            $failed = false;

            foreach ($definitions as $rule) {
                if (! $this->passesRule($rule, $attribute, $value, $present)) {
                    if ($this->fail($validated, $rule, $attribute)) {
                        return;
                    }

                    $failed = true;

                }
            }

            if (! $failed && $present) {
                $this->setValidatedValue($validated, $attribute, $value);
            }
        }

        $this->validated = new ValidatedData($validated, $this->dtoFactory);
        $this->evaluated = true;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function fail(array $validated, RuleDefinitionContract $rule, string $attribute): bool
    {
        $this->errors->add($attribute, $this->message($rule, $attribute));

        if (! $this->stopOnFirstFailure) {
            return false;
        }

        $this->validated = new ValidatedData($validated, $this->dtoFactory);
        $this->evaluated = true;

        return true;
    }

    /**
     * @return list<RuleDefinitionContract>
     */
    private function normalizeRules(mixed $rules): array
    {
        if ($rules instanceof RuleSetContract) {
            return $rules->rules();
        }

        if ($rules instanceof RuleDefinitionContract) {
            return [$rules];
        }

        if (! is_array($rules)) {
            return [];
        }

        $definitions = [];

        foreach ($rules as $rule) {
            if ($rule instanceof RuleSetContract) {
                array_push($definitions, ...$rule->rules());
            } elseif ($rule instanceof RuleDefinitionContract) {
                $definitions[] = $rule;
            }
        }

        return $definitions;
    }

    /**
     * @param  list<RuleDefinitionContract>  $rules
     * @param  class-string<RuleDefinitionContract>  $rule
     */
    private function hasRule(array $rules, string $rule): bool
    {
        return $this->firstRule($rules, $rule) !== null;
    }

    /**
     * @template TRule of RuleDefinitionContract
     *
     * @param  list<RuleDefinitionContract>  $rules
     * @param  class-string<TRule>  $rule
     * @return TRule|null
     */
    private function firstRule(array $rules, string $rule): ?RuleDefinitionContract
    {
        foreach ($rules as $definition) {
            if ($definition instanceof $rule) {
                return $definition;
            }
        }

        return null;
    }

    private function passesRule(RuleDefinitionContract $rule, string $attribute, mixed $value, bool $present): bool
    {
        return match (true) {
            $rule instanceof RequiredRule => $present && ! $this->empty($value),
            $rule instanceof OptionalRule,
            $rule instanceof NullableRule => true,
            $rule instanceof TypeRule => $this->passesType($rule, $value),
            $rule instanceof EmailRule => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            $rule instanceof MinRule => $this->measure($value) >= $rule->parameters()['min'],
            $rule instanceof MaxRule => $this->measure($value) <= $rule->parameters()['max'],
            $rule instanceof BetweenRule => $this->measure($value) >= $rule->parameters()['min']
                && $this->measure($value) <= $rule->parameters()['max'],
            $rule instanceof SameRule => $this->value((string) $rule->parameters()['field']) === $value,
            $rule instanceof ExistsRule => $this->presenceVerifier()
                ->exists(
                    (string) $rule->parameters()['table'],
                    (string) ($rule->parameters()['column'] ?? $attribute),
                    $value,
                ),
            $rule instanceof CustomRule => $this->passesCustomRule($rule, $attribute, $value),
            default => true,
        };
    }

    private function passesType(TypeRule $rule, mixed $value): bool
    {
        return match ($rule->parameters()['type']) {
            'string' => is_string($value),
            'integer' => is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value) === 1),
            'numeric' => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
            'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            'array' => is_array($value),
            default => true,
        };
    }

    private function passesCustomRule(CustomRule $rule, string $attribute, mixed $value): bool
    {
        $custom = $rule->parameters()['rule'];

        if (is_string($custom)) {
            $custom = new $custom;
        }

        if (! $custom instanceof ValidationRuleContract) {
            return false;
        }

        return $custom->passes($attribute, $value, $this->data);
    }

    private function message(RuleDefinitionContract $rule, string $attribute): string
    {
        $ruleName = $this->ruleName($rule);
        $message = $this->messages[$attribute.'.'.$ruleName]
            ?? $this->messages[$ruleName]
            ?? null;

        if (is_string($message) && $message !== '') {
            return str_replace(':attribute', $this->displayAttribute($attribute), $message);
        }

        if ($rule->message() !== null) {
            return $rule->message();
        }

        if ($rule instanceof CustomRule) {
            $custom = $rule->parameters()['rule'];
            $custom = is_string($custom) ? new $custom : $custom;

            if ($custom instanceof ValidationRuleContract) {
                return $custom->message($attribute);
            }
        }

        $display = $this->displayAttribute($attribute);

        return match (true) {
            $rule instanceof RequiredRule => "The $display field is required.",
            $rule instanceof TypeRule => "The $display field must be {$rule->parameters()['type']}.",
            $rule instanceof EmailRule => "The $display field must be a valid email address.",
            $rule instanceof MinRule => "The $display field must be at least {$rule->parameters()['min']}.",
            $rule instanceof MaxRule => "The $display field must not be greater than {$rule->parameters()['max']}.",
            $rule instanceof BetweenRule => "The $display field must be between {$rule->parameters()['min']} and {$rule->parameters()['max']}.",
            $rule instanceof SameRule => "The $display field must match {$rule->parameters()['field']}.",
            $rule instanceof ExistsRule => "The selected $display is invalid.",
            default => "The $display field is invalid.",
        };
    }

    private function displayAttribute(string $attribute): string
    {
        $display = $this->attributes[$attribute] ?? null;

        return is_string($display) && $display !== '' ? $display : $attribute;
    }

    private function ruleName(RuleDefinitionContract $rule): string
    {
        return match (true) {
            $rule instanceof RequiredRule => 'required',
            $rule instanceof OptionalRule => 'optional',
            $rule instanceof NullableRule => 'nullable',
            $rule instanceof TypeRule => (string) $rule->parameters()['type'],
            $rule instanceof EmailRule => 'email',
            $rule instanceof MinRule => 'min',
            $rule instanceof MaxRule => 'max',
            $rule instanceof BetweenRule => 'between',
            $rule instanceof SameRule => 'same',
            $rule instanceof ExistsRule => 'exists',
            $rule instanceof CustomRule => 'custom',
            default => 'invalid',
        };
    }

    private function empty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function measure(mixed $value): int|float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return strlen($value);
        }

        if (is_countable($value)) {
            return count($value);
        }

        return 0;
    }

    private function hasValue(string $attribute): bool
    {
        $value = $this->data;

        foreach (explode('.', $attribute) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    private function value(string $attribute): mixed
    {
        $value = $this->data;

        foreach (explode('.', $attribute) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function setValidatedValue(array &$validated, string $attribute, mixed $value): void
    {
        $target = &$validated;

        foreach (explode('.', $attribute) as $segment) {
            if (! isset($target[$segment]) || ! is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;
    }

    private function presenceVerifier(): PresenceVerifierContract
    {
        return $this->presenceVerifier ?? new NullPresenceVerifier;
    }
}
