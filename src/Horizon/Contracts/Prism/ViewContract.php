<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism;

interface ViewContract
{
    public function with(string|array $key, mixed $value = null): static;

    public function render(): string;

    public function __toString(): string;
}
