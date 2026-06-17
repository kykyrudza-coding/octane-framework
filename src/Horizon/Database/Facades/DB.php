<?php

declare(strict_types=1);

namespace Horizon\Database\Facades;

use Closure;
use Horizon\Contracts\Database\Connections\ConnectionContract;
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Support\Facades\Facade;

/**
 * @method static array  select(string $query, array $bindings = [])
 * @method static bool   insert(string $query, array $bindings = [])
 * @method static int    update(string $query, array $bindings = [])
 * @method static int    delete(string $query, array $bindings = [])
 * @method static mixed  raw(string $query, array $bindings = [])
 * @method static mixed  transaction(Closure $callback)
 * @method static void   beginTransaction()
 * @method static void   commit()
 * @method static void   rollback()
 */
final class DB extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConnectionManagerContract::class;
    }

    public static function connection(string $name = 'default'): ConnectionContract
    {
        return DB::resolve()->connection($name);
    }
}
