<?php

declare(strict_types=1);

namespace Horizon\Console\Commands;

use Horizon\Arch\Application;
use Horizon\Console\Command;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Console\ConsoleInputContract;
use Horizon\Contracts\Console\ConsoleOutputContract;
use Throwable;

final class AboutCommand extends Command
{
    public static function commandName(): string
    {
        return 'about';
    }

    public function description(): string
    {
        return 'Display application and framework information.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $app = Application::getInstance();

        $this->style->title('Octane Framework');

        $this->style->section('Application');
        $this->style->keyValue('Name', (string) $this->config('app.name', 'Octane Application'));
        $this->style->keyValue('Environment', $app->getEnvironment());
        $this->style->keyValue('Debug', $this->enabled($this->config('app.debug', false)));
        $this->style->keyValue('URL', (string) $this->config('app.url', ''));
        $this->style->keyValue('Timezone', (string) $this->config('app.timezone', date_default_timezone_get()));

        $this->style->section('Framework');
        $this->style->keyValue('Version', Application::version());
        $this->style->keyValue('Booted', $this->enabled($app->isBooted()));

        $this->style->section('Runtime');
        $this->style->keyValue('PHP', PHP_VERSION);
        $this->style->keyValue('SAPI', PHP_SAPI);
        $this->style->keyValue('OS', PHP_OS_FAMILY);

        $this->style->newLine();

        return self::SUCCESS;
    }

    private function config(string $key, mixed $default = null): mixed
    {
        try {
            $repository = Application::getInstance()->make(ConfigRepositoryContract::class);

            if ($repository instanceof ConfigRepositoryContract) {
                return $repository->get($key, $default);
            }
        } catch (Throwable) {
            return $default;
        }

        return $default;
    }

    private function enabled(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOL) ? 'enabled' : 'disabled';
    }
}
