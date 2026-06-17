<?php

declare(strict_types=1);

namespace Horizon\Database\Facades;

use Horizon\Contracts\Database\Schema\SchemaBuilderContract;
use Horizon\Support\Facades\Facade;

/**
 * @method static void create(string $table, array $columns)
 * @method static void alter(string $table, array $columns)
 * @method static void drop(string $table)
 * @method static void dropIfExists(string $table)
 * @method static void rename(string $from, string $to)
 * @method static bool hasTable(string $table)
 * @method static bool hasColumn(string $table, string $column)
 */
final class Migration extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SchemaBuilderContract::class;
    }
}
