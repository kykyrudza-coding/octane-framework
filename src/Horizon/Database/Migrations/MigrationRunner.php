<?php

declare(strict_types=1);

namespace Horizon\Database\Migrations;

use Horizon\Contracts\Database\Migrations\MigrationRepositoryContract;
use Horizon\Contracts\Database\Migrations\MigrationRunnerContract;
use Horizon\Contracts\Database\Migrations\Migratable;
use Horizon\Database\Pipelines\Migration\FilterPendingMigrations;
use Horizon\Database\Pipelines\Migration\ResolveMigrationFiles;
use Horizon\Database\Pipelines\Migration\RunMigrations;
use Horizon\Database\Pipelines\Migration\StoreMigrationRecord;
use Horizon\Support\Pipeline\Pipeline;
use RuntimeException;

final readonly class MigrationRunner implements MigrationRunnerContract
{
    public function __construct(
        private MigrationRepositoryContract $repository,
        private Pipeline                    $pipeline,
    ) {}

    public function run(string $path): void
    {
        $this->pipeline
            ->send(['path' => $path])
            ->through([
                ResolveMigrationFiles::class,
                FilterPendingMigrations::class,
                RunMigrations::class,
                StoreMigrationRecord::class,
            ])->then(fn (array $payload): array => $payload);

    }

    public function rollback(string $path, int $steps = 1): void
    {
        $ran   = $this->repository->getRan();
        $batch = $this->repository->getLastBatch();

        $toRollback = array_filter(
            $ran,
            fn (string $file): bool => $this->repository->getBatch($file) === $batch,
        );

        foreach (array_reverse($toRollback) as $file) {
            $migration = $this->resolve($path, $file);
            $migration->rollback();
            $this->repository->delete($file);

            $steps--;
            if ($steps === 0) {
                break;
            }
        }
    }

    public function fresh(string $path): void
    {
        $this->reset($path);
        $this->run($path);
    }

    public function reset(string $path): void
    {
        $ran = array_reverse($this->repository->getRan());

        foreach ($ran as $file) {
            $migration = $this->resolve($path, $file);
            $migration->rollback();
            $this->repository->delete($file);
        }
    }

    private function resolve(string $path, string $file): Migratable
    {
        $migration = require $path.'/'.$file;

        if (! $migration instanceof Migratable) {
            throw new RuntimeException(
                "Migration [{$file}] must implement Migratable.",
            );
        }

        return $migration;
    }
}
