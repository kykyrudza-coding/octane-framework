<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Column
{
    public function __construct(
        public string $name,
    ) {}
}
