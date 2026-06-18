<?php

declare(strict_types=1);

namespace Horizon\Dto\Attributes;

use Attribute;
use Horizon\Contracts\DTO\Casts\CastContract;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class CastWith
{
    /**
     * @param  class-string<CastContract>  $cast
     */
    public function __construct(
        public string $cast,
    ) {}
}
