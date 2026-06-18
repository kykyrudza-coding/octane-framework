<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Migration;

use Closure;
use Horizon\Contracts\Database\Migrations\MigrationRepositoryContract;
use Horizon\Database\Exceptions\MigrationException;
use Horizon\Support\Pipeline\PipeInterface;

final readonly class StoreMigrationRecord implements PipeInterface
{
    public function __construct(
        private MigrationRepositoryContract $repository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(mixed $payload, Closure $next): array
    {
        if (! is_array($payload)) {
            throw new MigrationException('Migration pipeline payload must be an array.');
        }

        $ran = $payload['ran'] ?? null;
        $batch = $payload['batch'] ?? null;

        if (! is_array($ran) || ! is_int($batch)) {
            throw new MigrationException('Migration pipeline payload missing [ran] or [batch].');
        }

        foreach ($ran as $file) {
            if (! is_string($file)) {
                throw new MigrationException('Migration filename must be a string.');
            }

            $this->repository->store($file, $batch);
        }

        $result = $next($payload);

        if (! is_array($result)) {
            throw new MigrationException('Migration pipeline result must be an array.');
        }

        $normalized = [];

        foreach ($result as $key => $value) {
            if (! is_string($key)) {
                throw new MigrationException('Migration pipeline result keys must be strings.');
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
