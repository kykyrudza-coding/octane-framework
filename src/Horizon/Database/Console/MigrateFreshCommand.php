<?php

declare(strict_types=1);

namespace Horizon\Database\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Arch\Application\ApplicationContract;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;
use Horizon\Contracts\Database\Migrations\MigrationRunnerContract;

final class MigrateFreshCommand extends Command
{
    public function __construct(
        private readonly MigrationRunnerContract $runner,
        private readonly ApplicationContract $app
    ) {}

    public static function commandName(): string
    {
        return 'migrate:fresh';
    }

    public function description(): string
    {
        return 'Drop all tables and re-run all migrations.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $path = $this->app->dbPath('migrations');

        $this->style->title('Fresh Migration');
        $this->style->warning('All tables will be dropped and recreated.');

        $this->runner->fresh($path);

        $this->style->success('Database refreshed successfully.');

        return self::SUCCESS;
    }
}
