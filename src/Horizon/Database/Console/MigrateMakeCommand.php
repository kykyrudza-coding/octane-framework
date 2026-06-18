<?php

declare(strict_types=1);

namespace Horizon\Database\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final class MigrateMakeCommand extends Command
{
    public function __construct(
        private readonly ApplicationContract $app
    ) {}

    public static function commandName(): string
    {
        return 'migrate:make';
    }

    public function description(): string
    {
        return 'Create a new migration file.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $name = $input->argument(2);

        if ($name === null) {
            $this->style->error('Migration name is required.');

            return self::FAILURE;
        }

        $path     = $this->app->dbPath('migrations');
        $filename = date('Y_m_d_His').'_'.$this->toSnakeCase($name).'.php';
        $fullPath = $path.'/'.$filename;

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($fullPath, $this->stub($name));

        $this->style->success("Migration created: <dim>{$filename}</dim>");

        return self::SUCCESS;
    }

    private function stub(string $name): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        use Horizon\Contracts\Database\Migrations\Migratable;

        return new class implements Migratable
        {
            public function run(): void
            {
                //
            }

            public function rollback(): void
            {
                //
            }
        };
        PHP;
    }

    private function toSnakeCase(string $name): string
    {
        return strtolower(
            preg_replace('/[A-Z]/', '_$0', lcfirst(
                str_replace([' ', '-'], '_', $name),
            )),
        );
    }
}
