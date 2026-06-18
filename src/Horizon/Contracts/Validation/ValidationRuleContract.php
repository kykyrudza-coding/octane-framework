<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

interface ValidationRuleContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function passes(string $attribute, mixed $value, array $data = []): bool;

    public function message(string $attribute): string;
}
