<?php

declare(strict_types=1);

namespace Horizon\QueryBuilder\Facades;

use Horizon\Support\Facades\Facade;
use Horizon\QueryBuilder\QueryBuilderFactory;

/**
 * @method static \Horizon\QueryBuilder\QueryBuilder table(string $table)
 * @method static \Horizon\QueryBuilder\QueryBuilder for(class-string $model)
 *
 * @see \Horizon\QueryBuilder\QueryBuilderFactory
 */
final class QueryBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QueryBuilderFactory::class;
    }

    /**
     * Forward static table() / for() calls directly to a fresh builder instance.
     */
    public static function table(string $table): \Horizon\QueryBuilder\QueryBuilder
    {
        return QueryBuilder::getFacadeRoot()->forTable($table);
    }

    public static function for(string $model): \Horizon\QueryBuilder\QueryBuilder
    {
        return QueryBuilder::getFacadeRoot()->forModel($model);
    }
}
