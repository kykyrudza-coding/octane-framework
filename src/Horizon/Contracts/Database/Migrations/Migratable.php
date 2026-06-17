<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Migrations;

interface Migratable
{
    public function run(): void;

    public function rollback(): void;
}
