<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Migration;

use Closure;
use Horizon\Database\Exceptions\MigrationException;
use Horizon\Support\Pipeline\PipeInterface;

final class ResolveMigrationFiles implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): array
    {
        $path = $payload['path'];

        if (! is_dir($path)) {
            throw new MigrationException(
                "Migrations directory not found: [$path].",
            );
        }

        $files = glob($path.'/*.php') ?: [];

        sort($files);

        $payload['files'] = array_map(
            fn (string $file): string => basename($file),
            $files,
        );

        return $next($payload);
    }
}
