<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

interface ValidatorFactoryContract
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     */
    public function make(array $data, array $rules): ValidatorContract;
}
