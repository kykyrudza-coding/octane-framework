<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\Http\Request\RequestContract;
use Horizon\Contracts\Validation\FormRequestContract;
use Horizon\Contracts\Validation\ValidatedDataContract;
use Horizon\Contracts\Validation\ValidatorFactoryContract;
use Horizon\Http\Request\Request;
use Horizon\Validation\Exceptions\AuthorizationException;
use Horizon\Validation\Exceptions\FormRequestException;

abstract class FormRequest extends Request implements FormRequestContract
{
    private ?ValidatedDataContract $validated = null;

    public function __construct(
        ?RequestContract $request = null,
        private readonly ?ValidatorFactoryContract $validatorFactory = null,
    ) {
        parent::__construct();

        if ($request !== null) {
            $this->replace(
                query: $request->allQuery(),
                payload: $request->allPayload(),
            );
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function dto(): ?string
    {
        return null;
    }

    public function validateResolved(): ValidatedDataContract
    {
        if ($this->validated !== null) {
            return $this->validated;
        }

        if (! $this->authorize()) {
            throw new AuthorizationException;
        }

        return $this->validated = $this->validatorFactory()
            ->make($this->all(), $this->rules())
            ->validate();
    }

    public function validated(): ValidatedDataContract
    {
        return $this->validateResolved();
    }

    public function toDto(?string $dto = null): DtoContract
    {
        $dto ??= $this->dto();

        if ($dto === null) {
            throw new FormRequestException('Form request does not define a DTO class.');
        }

        return $this->validated()->toDto($dto);
    }

    private function validatorFactory(): ValidatorFactoryContract
    {
        return $this->validatorFactory ?? new ValidatorFactory;
    }
}
