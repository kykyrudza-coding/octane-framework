<?php

declare(strict_types=1);

namespace Horizon\Http\Console;

use Horizon\Console\Command;
use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;

final class StartServerCommand extends Command
{
    private const string DEFAULT_HOST = '127.0.0.1';
    private const int DEFAULT_PORT = 8000;

    public function __construct(
        private readonly ApplicationContract $app,
    ) {}

    public static function commandName(): string
    {
        return 'start';
    }

    public function description(): string
    {
        return 'Start the Octane development server.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $host    = $input->argument(2, self::DEFAULT_HOST);
        $port    = (int) $input->argument(3, self::DEFAULT_PORT);
        $docroot = $this->app->basePath('public');

        if (! is_dir($docroot)) {
            $this->style->error("Document root not found: $docroot");
            return self::FAILURE;
        }

        $this->style->title('Octane Development Server');
        $this->style->keyValue('URL',  "http://$host:$port");
        $this->style->keyValue('Root', $docroot);
        $this->style->keyValue('PHP',  PHP_VERSION);
        $this->style->info('Press Ctrl+C to stop the server.');
        $this->style->newLine();

        $command = sprintf(
            '%s -S %s:%d -t %s',
            PHP_BINARY,
            escapeshellarg($host),
            $port,
            escapeshellarg($docroot),
        );

        $process = proc_open(
            $command,
            [
                0 => STDIN,
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            $this->style->error('Failed to start server process.');
            return self::FAILURE;
        }

        stream_set_blocking($pipes[2], false);

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () use ($process, $pipes): void {
                fclose($pipes[2]);
                proc_terminate($process);
                $this->style->newLine();
                $this->style->info('Server stopped.');
                exit(0);
            });
            pcntl_async_signals(true);
        }

        while (is_resource($process)) {
            $line = fgets($pipes[2]);

            if ($line !== false) {
                $this->formatServerLog(trim($line), $output);
            }

            $status = proc_get_status($process);
            if (! $status['running']) {
                break;
            }

            usleep(10_000);
        }

        fclose($pipes[2]);
        proc_close($process);

        return self::SUCCESS;
    }

    private function formatServerLog(string $line, ConsoleOutputContract $output): void
    {
        if ($line === '') {
            return;
        }

        if (str_contains($line, 'Development Server') && str_contains($line, 'started')) {
            $this->style->success('Server started successfully.');
            return;
        }

        if (preg_match('/\[(.+?)\] ([\d.]+:\d+) \[(\d+)\]: (\w+) (.+)/', $line, $m)) {
            [, $datetime, $remote, $status, $method, $path] = $m;

            $time       = date('H:i:s', strtotime($datetime));
            $statusTag  = $this->statusTag((int) $status);
            $methodTag  = "<info>{$method}</info>";

            $output->line("  <dim>{$time}</dim>  {$statusTag}  {$methodTag} <bold>{$path}</bold>  <dim>{$remote}</dim>");
            return;
        }

        if (str_contains($line, 'Accepted') || str_contains($line, 'Closing')) {
            return;
        }

        if (str_contains($line, 'PHP Fatal') || str_contains($line, 'PHP Warning') || str_contains($line, 'PHP Notice')) {
            $this->style->error($line);
            return;
        }

        $output->line("<dim>{$line}</dim>");
    }

    private function statusTag(int $status): string
    {
        return match (true) {
            $status >= 500 => "<error> {$status} </error>",
            $status >= 400 => "<warning> {$status} </warning>",
            $status >= 300 => "<info> {$status} </info>",
            default        => "<success> {$status} </success>",
        };
    }
}
