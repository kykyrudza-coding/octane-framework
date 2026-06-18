<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

use Countable;
use Horizon\Contracts\Support\Arrayable;
use Horizon\Contracts\Support\Jsonable;

interface ValidationErrorBagContract extends Arrayable, Countable, Jsonable
{
    public function add(string $attribute, string $message): void;

    public function has(string $attribute): bool;

    public function first(?string $attribute = null): ?string;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    /**
     * @return array<string, list<string>>
     */
    public function all(): array;
}
