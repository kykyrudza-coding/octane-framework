<?php

declare(strict_types=1);

namespace Horizon\Database\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;
use Horizon\Contracts\Database\Migrations\MigrationRunnerContract;

final class MigrateResetCommand extends Command
{
    public function __construct(
        private readonly MigrationRunnerContract $runner,
        private readonly ApplicationContract $app
    ) {}

    public static function commandName(): string
    {
        return 'migrate:reset';
    }

    public function description(): string
    {
        return 'Rollback all migrations.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $path = $this->app->dbPath('migrations');

        $this->style->title('Migration Reset');
        $this->style->warning('All migrations will be rolled back.');

        $this->runner->reset($path);

        $this->style->success('All migrations rolled back successfully.');

        return self::SUCCESS;
    }
}
