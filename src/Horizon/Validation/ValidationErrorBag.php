<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\Validation\ValidationErrorBagContract;

final class ValidationErrorBag implements ValidationErrorBagContract
{
    /**
     * @var array<string, list<string>>
     */
    private array $errors = [];

    public function add(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    public function has(string $attribute): bool
    {
        return isset($this->errors[$attribute]);
    }

    public function first(?string $attribute = null): ?string
    {
        if ($attribute !== null) {
            return $this->errors[$attribute][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return $this->errors === [];
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    public function all(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public function count(): int
    {
        return array_sum(array_map('count', $this->errors));
    }
}
