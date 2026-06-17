<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Connection;

use Closure;
use Horizon\Database\Connections\Connection;
use Horizon\Support\Pipeline\PipeInterface;

final class EstablishConnection implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): array
    {
        $pdo = $payload['driver']->connect($payload['config']);

        $payload['connection'] = new Connection(
            $pdo,
            $payload['name'],
            $payload['driver']->getName(),
        );

        return $next($payload);
    }
}
