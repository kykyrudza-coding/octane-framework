<?php

declare(strict_types=1);

namespace Horizon\Dto\Attributes;

use Attribute;
use Horizon\Contracts\DTO\DtoContract;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class CollectionOf
{
    /**
     * @param  class-string<DtoContract>  $dto
     */
    public function __construct(
        public string $dto,
    ) {}
}
