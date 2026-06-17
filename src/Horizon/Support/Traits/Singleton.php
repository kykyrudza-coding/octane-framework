<?php

declare(strict_types=1);

namespace Horizon\Support\Traits;

trait Singleton
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function resetInstance(): void
    {
        static::$instance = null;
    }
}
