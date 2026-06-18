<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\Http\Request\RequestContract;

interface FormRequestContract extends RequestContract
{
    public function authorize(): bool;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * @return class-string<DtoContract>|null
     */
    public function dto(): ?string;

    public function validateResolved(): ValidatedDataContract;

    public function validated(): ValidatedDataContract;

    /**
     * @template TDto of DtoContract
     *
     * @param  class-string<TDto>|null  $dto
     * @return TDto
     */
    public function toDto(?string $dto = null): DtoContract;
}
