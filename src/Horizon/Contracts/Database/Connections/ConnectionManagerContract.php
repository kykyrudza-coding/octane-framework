<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Connections;

interface ConnectionManagerContract
{
    public function connection(string $name = 'default'): ConnectionContract;

    public function extend(string $driver, string $driverClass): void;

    public function reconnect(string $name = 'default'): ConnectionContract;

    public function disconnect(string $name = 'default'): void;
}
