<?php

declare(strict_types=1);

namespace Horizon\Support\Facades;

use Horizon\Arch\Application;
use RuntimeException;

abstract class Facade
{
    protected static function getFacadeAccessor(): string
    {
        throw new RuntimeException(
            'Facade does not implement getFacadeAccessor().'
        );
    }

    public static function __callStatic(string $method, array $arguments)
    {
        return Application::getInstance()
            ->make(static::getFacadeAccessor())->$method(...$arguments);
    }
}
