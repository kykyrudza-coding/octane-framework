<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Migration;

use Closure;
use Horizon\Contracts\Database\Migrations\Migratable;
use Horizon\Support\Pipeline\PipeInterface;
use RuntimeException;

final class RunMigrations implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): array
    {
        $path    = $payload['path'];
        $pending = $payload['pending'];

        $payload['ran'] = [];

        foreach ($pending as $file) {
            $migration = require $path.'/'.$file;

            if (! $migration instanceof Migratable) {
                throw new RuntimeException(
                    "Migration [$file] must implement Migratable.",
                );
            }

            $migration->run();

            $payload['ran'][] = $file;
        }

        return $next($payload);
    }
}
