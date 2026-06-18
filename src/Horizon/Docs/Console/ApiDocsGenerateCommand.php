<?php

declare(strict_types=1);

namespace Horizon\Docs\Console;

use FilesystemIterator;
use Horizon\Arch\Application;
use Horizon\Console\Command;
use Horizon\Contracts\Console\Input\ConsoleInputContract;
use Horizon\Contracts\Console\Output\ConsoleOutputContract;
use Horizon\Docs\ApiDocGenerator;
use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

final class ApiDocsGenerateCommand extends Command
{
    public static function commandName(): string
    {
        return 'docs:api';
    }

    public function description(): string
    {
        return 'Generate Octane API reference pages.';
    }

    public function handle(
        ConsoleInputContract $input,
        ConsoleOutputContract $output,
    ): int {
        $app = Application::getInstance();
        $source = $input->argument(2, $this->frameworkSourcePath());
        $target = $input->argument(3, $app->varPath('framework/api-docs'));

        if (! is_string($source) || ! is_string($target)) {
            $this->style->error('Invalid source or target path.');

            return self::FAILURE;
        }

        $this->style->title('Octane API Documentation');
        $this->style->keyValue('Source', $source);
        $this->style->keyValue('Target', $target);
        $this->style->newLine();

        try {
            $this->warmup('Preparing documentation build');
            $this->indexSourceFiles($source);

            $classes = $this->style->spinner()->run(
                'Generating Octane API pages',
                function () use ($source, $target): array {
                    $this->pause(180000);

                    return (new ApiDocGenerator)->generate($source, $target);
                }
            );
        } catch (Throwable $exception) {
            $this->style->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->style->newLine();
        $this->style->table()
            ->setHeaders(['Metric', 'Value'])
            ->addRow(['Classes', (string) count($classes)])
            ->addRow(['Entry', '/_octane/api'])
            ->addRow(['Output', $target])
            ->render();

        $this->style->newLine();
        $this->style->success('API documentation generated successfully.');

        return self::SUCCESS;
    }

    private function frameworkSourcePath(): string
    {
        $reflection = new ReflectionClass(Application::class);
        $file = $reflection->getFileName();

        if (! is_string($file)) {
            return Application::getInstance()->basePath('src/Horizon');
        }

        return dirname($file, 2);
    }

    private function warmup(string $label): void
    {
        $spinner = $this->style->spinner();
        $spinner->start($label);

        for ($i = 0; $i < 10; $i++) {
            $this->pause(65000);
            $spinner->tick($label);
        }

        $spinner->success($label);
    }

    private function indexSourceFiles(string $source): void
    {
        $files = $this->phpFiles($source);
        $total = count($files);

        $this->style->section('Indexing source files');

        if ($total === 0) {
            $this->style->warning('No PHP source files were found.');

            return;
        }

        $progress = $this->style->progress($total);

        foreach ($files as $file) {
            $this->pause(9000);
            $progress->advance();
        }

        $progress->finish();
        $this->style->info("Indexed {$total} PHP files.");
        $this->style->newLine();
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $source): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function pause(int $microseconds): void
    {
        usleep($microseconds);
    }
}
