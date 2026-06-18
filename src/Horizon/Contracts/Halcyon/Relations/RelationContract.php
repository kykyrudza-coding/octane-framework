<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon\Relations;

interface RelationContract
{
    public function getName(): string;

    /**
     * @return class-string
     */
    public function getRelated(): string;

    public function getForeignKey(): string;

    public function getLocalKey(): string;
}
