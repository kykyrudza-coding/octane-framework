<?php

declare(strict_types=1);

namespace Horizon\Contracts\Database\Seeder;

interface SeederContract
{
    public function run(): void;
}
