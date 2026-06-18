<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

interface RuleSetContract
{
    public function add(RuleDefinitionContract $rule): static;

    /**
     * @return list<RuleDefinitionContract>
     */
    public function rules(): array;

    /**
     * @return list<string>
     */
    public function names(): array;
}
