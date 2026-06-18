<?php

declare(strict_types=1);

namespace Horizon\Dto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class MapFrom
{
    public function __construct(
        public string $name,
    ) {}
}
