<?php

declare(strict_types=1);

namespace Horizon\Support\Facades;

use Horizon\QueryBuilder\QueryBuilder;
use Horizon\QueryBuilder\QueryBuilderFactory;

/**
 * @see QueryBuilderFactory
 */
final class QB extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QueryBuilderFactory::class;
    }

    /**
     * Forward static table() / for() calls directly to a fresh builder instance.
     */
    public static function table(string $table): QueryBuilder
    {
        return Facade::getFacadeRoot()->forTable($table);
    }

    public static function for(string $model): QueryBuilder
    {
        return Facade::getFacadeRoot()->forModel($model);
    }
}
