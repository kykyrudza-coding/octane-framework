<?php

declare(strict_types=1);

namespace Horizon\Database\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Arch\Application\ApplicationContract;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;
use Horizon\Contracts\Database\Migrations\MigrationRunnerContract;

final class MigrateCommand extends Command
{
    public function __construct(
        private readonly MigrationRunnerContract $runner,
        private readonly ApplicationContract $app
    ) {}

    public static function commandName(): string
    {
        return 'migrate';
    }

    public function description(): string
    {
        return 'Run pending database migrations.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $path = $this->app->dbPath('migrations');

        $this->style->title('Database Migrations');

        $this->runner->run($path);

        $this->style->success('Migrations ran successfully.');

        return self::SUCCESS;
    }
}
