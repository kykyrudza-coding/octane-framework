<?php

declare(strict_types=1);

namespace Tests\Validation;

use Horizon\Arch\Application;
use Horizon\Contracts\Validation\ValidationRuleContract;
use Horizon\Contracts\Validation\ValidatorFactoryContract;
use Horizon\Dto\DataTransferObject;
use Horizon\Validation\Exceptions\ValidationException;
use Horizon\Validation\Presence\ArrayPresenceVerifier;
use Horizon\Validation\Providers\ValidationServiceProvider;
use Horizon\Validation\Rule;
use Horizon\Validation\Validator;
use Horizon\Validation\ValidatorFactory;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_validator_returns_validated_data_for_passing_rules(): void
    {
        $validated = Validator::make([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'ignored' => 'value',
        ], [
            'name' => Rule::required()->string()->min(3),
            'email' => Rule::required()->email(),
            'age' => Rule::optional()->integer(),
        ])->validate();

        $this->assertSame([
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ], $validated->all());
        $this->assertSame('Ada', $validated->get('name'));
        $this->assertFalse($validated->has('ignored'));
    }

    public function test_validator_throws_validation_exception_with_error_bag(): void
    {
        $validator = Validator::make([
            'name' => 'Al',
            'email' => 'not-an-email',
        ], [
            'name' => Rule::required()->string()->min(3),
            'email' => Rule::required()->email(),
            'password' => Rule::required(),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame('The name field must be at least 3.', $validator->errors()->first('name'));

        try {
            $validator->validate();
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertTrue($exception->errors()->has('email'));
            $this->assertTrue($exception->errors()->has('password'));
            $this->assertSame(3, $exception->errors()->count());
        }
    }

    public function test_required_rule_has_priority_over_other_rules(): void
    {
        $validator = Validator::make([], [
            'name' => Rule::string()->required(),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame('The name field is required.', $validator->errors()->first('name'));
    }

    public function test_nullable_values_skip_remaining_rules(): void
    {
        $validated = Validator::make([
            'bio' => null,
        ], [
            'bio' => Rule::nullable()->string()->min(5),
        ])->validate();

        $this->assertSame(['bio' => null], $validated->all());
    }

    public function test_custom_rule_and_custom_message_are_supported(): void
    {
        $validator = Validator::make([
            'password' => 'short',
        ], [
            'password' => Rule::required()->rule(StrongPasswordRule::class),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame('The password field is too weak.', $validator->errors()->first('password'));
    }

    public function test_exists_rule_uses_presence_verifier(): void
    {
        $factory = new ValidatorFactory(new ArrayPresenceVerifier([
            'users' => [
                ['id' => 1, 'email' => 'ada@example.com'],
            ],
        ]));

        $passing = $factory->make([
            'email' => 'ada@example.com',
        ], [
            'email' => Rule::required()->exists('users', 'email'),
        ]);

        $failing = $factory->make([
            'email' => 'missing@example.com',
        ], [
            'email' => Rule::required()->exists('users', 'email'),
        ]);

        $this->assertTrue($passing->passes());
        $this->assertTrue($failing->fails());
        $this->assertSame('The selected email is invalid.', $failing->errors()->first('email'));
    }

    public function test_validated_data_can_map_to_dto(): void
    {
        $dto = Validator::make([
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ], [
            'name' => Rule::required()->string(),
            'email' => Rule::required()->email(),
        ])->validate()->toDto(ValidatedUserDto::class);

        $this->assertInstanceOf(ValidatedUserDto::class, $dto);
        $this->assertSame('Ada', $dto->name);
    }

    public function test_service_provider_registers_validator_factory(): void
    {
        $app = new Application;
        $app->registerProvider(new ValidationServiceProvider($app));

        $factory = $app->make(ValidatorFactoryContract::class);

        $this->assertInstanceOf(ValidatorFactoryContract::class, $factory);
        $this->assertTrue($factory->make([
            'name' => 'Ada',
        ], [
            'name' => Rule::required()->string(),
        ])->passes());
    }
}

final class StrongPasswordRule implements ValidationRuleContract
{
    public function passes(string $attribute, mixed $value, array $data = []): bool
    {
        return is_string($value) && strlen($value) >= 8;
    }

    public function message(string $attribute): string
    {
        return "The $attribute field is too weak.";
    }
}

final class ValidatedUserDto extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
