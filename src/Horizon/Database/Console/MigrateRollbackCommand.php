<?php

declare(strict_types=1);

namespace Horizon\Database\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;
use Horizon\Contracts\Database\Migrations\MigrationRunnerContract;

final class MigrateRollbackCommand extends Command
{
    public function __construct(
        private readonly MigrationRunnerContract $runner,
        private readonly ApplicationContract $app
    ) {}

    public static function commandName(): string
    {
        return 'migrate:rollback';
    }

    public function description(): string
    {
        return 'Rollback the last batch of migrations.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $path  = $this->app->dbPath('migrations');
        $steps = (int) $input->argument(2, 1);

        $this->style->title('Migration Rollback');

        $this->runner->rollback($path, $steps);

        $this->style->success('Rollback completed successfully.');

        return self::SUCCESS;
    }
}
