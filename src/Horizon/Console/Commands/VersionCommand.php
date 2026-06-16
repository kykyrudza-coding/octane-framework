<?php

declare(strict_types=1);

namespace Horizon\Console\Commands;

use Horizon\Arch\Application;
use Horizon\Console\Command;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;

final class VersionCommand extends Command
{
    public function name(): string
    {
        return 'version';
    }

    public function description(): string
    {
        return 'Display Octane version.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $this->style->title('Octane Framework');

        $this->style->keyValue('Version', Application::version());
        $this->style->keyValue('PHP', PHP_VERSION);

        return self::SUCCESS;
    }
}
