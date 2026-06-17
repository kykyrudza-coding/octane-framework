<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Connections;

interface ConnectionFactoryContract
{
    /**
     * @param array<string, mixed> $config
     */
    public function make(string $name, array $config): ConnectionContract;
}
