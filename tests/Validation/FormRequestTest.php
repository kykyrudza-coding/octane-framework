<?php

declare(strict_types=1);

namespace Tests\Validation;

use Horizon\Arch\Container;
use Horizon\Arch\Http\Pipes\InvokeController;
use Horizon\Contracts\Validation\ValidatorFactoryContract;
use Horizon\Dto\DataTransferObject;
use Horizon\Http\Request\Request;
use Horizon\Http\Request\RequestContext;
use Horizon\Http\Response\Response;
use Horizon\Routing\RouteDTO;
use Horizon\Validation\Exceptions\AuthorizationException;
use Horizon\Validation\Exceptions\ValidationException;
use Horizon\Validation\FormRequest;
use Horizon\Validation\Rule;
use Horizon\Validation\ValidatorFactory;
use PHPUnit\Framework\TestCase;

final class FormRequestTest extends TestCase
{
    public function test_request_validate_returns_validated_data(): void
    {
        $request = new Request;
        $request->replace(payload: [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'ignored' => 'value',
        ]);

        $validated = $request->validate([
            'name' => Rule::required()->string(),
            'email' => Rule::required()->email(),
        ]);

        $this->assertSame([
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ], $validated->all());
    }

    public function test_form_request_validates_and_maps_to_configured_dto(): void
    {
        $base = new Request;
        $base->replace(payload: [
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $request = new CreateUserFormRequest($base);

        $this->assertSame([
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ], $request->validated()->all());

        $dto = $request->toDto();

        $this->assertInstanceOf(CreateUserDto::class, $dto);
        $this->assertSame('Ada', $dto->name);
    }

    public function test_form_request_throws_validation_exception(): void
    {
        $base = new Request;
        $base->replace(payload: [
            'name' => 'Al',
            'email' => 'invalid',
        ]);

        $this->expectException(ValidationException::class);

        (new CreateUserFormRequest($base))->validateResolved();
    }

    public function test_form_request_authorization_can_fail(): void
    {
        $this->expectException(AuthorizationException::class);

        (new UnauthorizedFormRequest(new Request))->validateResolved();
    }

    public function test_controller_receives_validated_form_request(): void
    {
        $request = new Request;
        $request->replace(payload: [
            'name' => 'Ada',
            'email' => 'ada@example.com',
        ]);

        $context = new RequestContext($request);
        $context->setRoute(new RouteDTO(
            methods: ['POST'],
            uri: '/users',
            action: [CreateUserController::class, 'store'],
        ));

        $container = new Container;
        $container->singleton(ValidatorFactoryContract::class, ValidatorFactory::class);

        $response = (new InvokeController($container))->handle(
            $context,
            fn (RequestContext $context): Response => $context->getResponse(),
        );

        $this->assertSame('Ada:ada@example.com', $response->getBody());
    }
}

final class CreateUserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => Rule::required()->string()->min(3),
            'email' => Rule::required()->email(),
        ];
    }

    public function dto(): ?string
    {
        return CreateUserDto::class;
    }
}

final class UnauthorizedFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [];
    }
}

final class CreateUserDto extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}

final class CreateUserController
{
    public function store(CreateUserFormRequest $request): Response
    {
        /** @var CreateUserDto $dto */
        $dto = $request->toDto();

        return new Response($dto->name.':'.$dto->email);
    }
}
