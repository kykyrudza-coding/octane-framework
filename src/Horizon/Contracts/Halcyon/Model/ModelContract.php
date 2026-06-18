<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Model;

interface ModelContract
{
    public function setRelation(string $name, object $items): void;

    public function getRelation(string $name): object;

    public function relationLoaded(string $name): bool;
}
