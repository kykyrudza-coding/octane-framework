<?php

declare(strict_types=1);

namespace Horizon\Support;

use BadMethodCallException;

trait Macroable
{
    protected static array $macros = [];

    public static function macro(string $name, object|callable $macro): void
    {
        self::$macros[$name] = $macro;
    }

    public static function hasMacro(string $name): bool
    {
        return isset(self::$macros[$name]);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (!static::hasMacro($name)) {
            throw new BadMethodCallException('Method ' . $name . ' does not exist.');
        }

        return (static::$macros[$name])(...$arguments);
    }

    public function __call(string $name, array $arguments)
    {
        if (!static::hasMacro($name)) {
            throw new BadMethodCallException('Method ' . $name . ' does not exist.');
        }

        return (static::$macros[$name])(...$arguments);
    }
}
