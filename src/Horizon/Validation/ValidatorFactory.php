<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\Validation\PresenceVerifierContract;
use Horizon\Contracts\Validation\ValidatorContract;
use Horizon\Contracts\Validation\ValidatorFactoryContract;

final readonly class ValidatorFactory implements ValidatorFactoryContract
{
    public function __construct(
        private ?PresenceVerifierContract $presenceVerifier = null,
        private ?DtoFactoryContract $dtoFactory = null,
    ) {}

    public function make(array $data, array $rules): ValidatorContract
    {
        return new Validator($data, $rules, $this->presenceVerifier, $this->dtoFactory);
    }
}
