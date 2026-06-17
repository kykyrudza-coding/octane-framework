<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Migration;

use Closure;
use Horizon\Contracts\Database\Migrations\MigrationRepositoryContract;
use Horizon\Support\Pipeline\PipeInterface;

final readonly class FilterPendingMigrations implements PipeInterface
{
    public function __construct(
        private MigrationRepositoryContract $repository,
    ) {}

    public function handle(mixed $payload, Closure $next): array
    {
        if (! $this->repository->tableExists()) {
            $this->repository->createTable();
        }

        $payload['pending'] = $this->repository->getPending($payload['files']);
        $payload['batch']   = $this->repository->getLastBatch() + 1;

        return $next($payload);
    }
}
