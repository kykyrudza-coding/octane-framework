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
        private bool $stopOnFirstFailure = false,
        private array $messages = [],
        private array $attributes = [],
    ) {}

    public function make(array $data, array $rules): ValidatorContract
    {
        return new Validator(
            data: $data,
            rules: $rules,
            presenceVerifier: $this->presenceVerifier,
            dtoFactory: $this->dtoFactory,
            stopOnFirstFailure: $this->stopOnFirstFailure,
            messages: $this->messages,
            attributes: $this->attributes,
        );
    }
}
