<?php

declare(strict_types=1);

namespace Horizon\Database\Pipelines\Connection;

use Closure;
use Horizon\Database\Exceptions\DatabaseConfigurationException;
use Horizon\Support\Pipeline\PipeInterface;

final class BuildConnectionConfig implements PipeInterface
{
    private const array REQUIRED = ['driver', 'database'];

    public function handle(mixed $payload, Closure $next): array
    {
        foreach (self::REQUIRED as $key) {
            if (empty($payload['config'][$key])) {
                throw new DatabaseConfigurationException(
                    "Database connection config missing required field [$key].",
                );
            }
        }

        $payload['config'] = array_merge(
            $this->defaults($payload['config']['driver']),
            $payload['config'],
        );

        return $next($payload);
    }

    private function defaults(string $driver): array
    {
        return match ($driver) {
            'mysql'  => [
                'host'    => '127.0.0.1',
                'port'    => 3306,
                'charset' => 'utf8mb4',
                'options' => [
                    'strict'    => true,
                    'timeout'   => 5,
                    'reconnect' => true,
                ],
            ],
            'pgsql'  => [
                'host'    => '127.0.0.1',
                'port'    => 5432,
                'charset' => 'utf8',
                'schema'  => 'public',
                'options' => [
                    'timeout'   => 5,
                    'reconnect' => true,
                ],
            ],
            'sqlite' => [
                'database' => ':memory:',
                'options'  => [],
            ],
            default => [],
        };
    }
}
