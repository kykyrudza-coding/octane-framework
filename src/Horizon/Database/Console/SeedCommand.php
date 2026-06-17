<?php

declare(strict_types=1);

namespace Horizon\Database\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;
use Horizon\Contracts\Database\Seeder\SeederContract;

final class SeedCommand extends Command
{
    public static function commandName(): string
    {
        return 'db:seed';
    }

    public function description(): string
    {
        return 'Seed the database with records.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $seederClass = $input->argument(2, 'DB\\Seeders\\DatabaseSeeder');

        if (! class_exists($seederClass)) {
            $this->style->error("Seeder [$seederClass] not found.");

            return self::FAILURE;
        }

        $seeder = new $seederClass();

        if (! $seeder instanceof SeederContract) {
            $this->style->error("Seeder [$seederClass] must implement SeederContract.");

            return self::FAILURE;
        }

        $this->style->title('Database Seeder');

        $seeder->run();

        $this->style->success('Database seeded successfully.');

        return self::SUCCESS;
    }
}
