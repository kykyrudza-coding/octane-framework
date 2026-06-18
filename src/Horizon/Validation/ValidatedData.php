<?php

declare(strict_types=1);

namespace Horizon\Validation;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\Validation\ValidatedDataContract;
use Horizon\Dto\DtoFactory;

final readonly class ValidatedData implements ValidatedDataContract
{
    /**
     * @param  array<string, mixed>  $items
     */
    public function __construct(
        private array $items,
        private ?DtoFactoryContract $dtoFactory = null,
    ) {}

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function has(string $key): bool
    {
        if (array_key_exists($key, $this->items)) {
            return true;
        }

        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    public function only(array $keys): array
    {
        $items = [];

        foreach ($keys as $key) {
            if ($this->has($key)) {
                $items[$key] = $this->get($key);
            }
        }

        return $items;
    }

    public function except(array $keys): array
    {
        $items = $this->items;

        foreach ($keys as $key) {
            unset($items[$key]);
        }

        return $items;
    }

    public function toDto(string $dto): DtoContract
    {
        return ($this->dtoFactory ?? new DtoFactory)->make($dto, $this->items);
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
