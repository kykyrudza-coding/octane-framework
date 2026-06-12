<?php

declare(strict_types=1);

namespace Horizon\Contracts\Prism;

interface ViewFactoryContract
{
    public function make(string $view, array $data = []): ViewContract;

    public function exists(string $view): bool;

    public function share(string|array $key, mixed $value = null): void;

    public function getShared(): array;
}
