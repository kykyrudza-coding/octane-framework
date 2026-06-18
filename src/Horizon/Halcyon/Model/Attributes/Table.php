<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Model\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Table
{
    public function __construct(
        public string $name
    ){}
}
