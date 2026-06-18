<?php

declare(strict_types=1);

namespace Horizon\Database\Seeders;

use Horizon\Contracts\Database\Seeders\SeederContract;

abstract class Seeder implements SeederContract
{
    protected function call(string ...$seeders ): void
    {
        foreach ($seeders  as $seeder) {
            new $seeder()->run();
        }
    }
}
