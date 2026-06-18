<?php

declare(strict_types=1);

namespace Horizon\Arch\Config;

use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;

class ConfigRepository implements ConfigRepositoryContract
{
    /**
     * @var array<string, mixed>
     */
    protected array $items = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        $value = $this->items;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return $default;
            }
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }
}
