<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Model;

use Horizon\Contracts\Halcyon\Model\ModelContract;
use Horizon\Halcyon\Exceptions\RelationNotLoadedException;

abstract class Model implements ModelContract
{
    /**
     * @var array <string, object>
     */
    protected array $loadedRelations = [];

    public function setRelation(string $name, object $items): void
    {
        $this->loadedRelations[$name] = $items;
    }

    public function getRelation(string $name): object
    {
        if (! $this->relationLoaded($name)) {
            throw new RelationNotLoadedException($name, static::class);
        }

        return $this->loadedRelations[$name];
    }

    public function relationLoaded(string $name): bool
    {
        return isset($this->loadedRelations[$name]);
    }

    protected static function hidden(): array
    {
        return [];
    }

    protected static function casts(): array
    {
        return [];
    }

    protected static function observers(): array
    {
        return [];
    }

    protected static function scopes(): array
    {
        return [];
    }
}
