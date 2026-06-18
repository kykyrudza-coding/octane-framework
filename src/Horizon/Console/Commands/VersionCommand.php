<?php

declare(strict_types=1);

namespace Horizon\Console\Commands;

use Horizon\Arch\Application;
use Horizon\Console\Command;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final class VersionCommand extends Command
{
    public static function commandName(): string
    {
        return 'version';
    }

    public function description(): string
    {
        return 'Display Octane version information.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $this->style->title('Octane Framework');

        $this->style->keyValue('Version', Application::version());
        $this->style->keyValue('PHP',     PHP_VERSION);
        $this->style->keyValue('OS',      PHP_OS_FAMILY);
        $this->style->keyValue('SAPI',    PHP_SAPI);

        $this->style->newLine();

        return self::SUCCESS;
    }
}
