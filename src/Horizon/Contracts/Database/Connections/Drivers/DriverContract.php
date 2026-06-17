<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Connections\Drivers;

use PDO;

interface DriverContract
{
    /**
     * @param array<string, mixed> $config
     */
    public function connect(array $config): PDO;

    public function getName(): string;
}
