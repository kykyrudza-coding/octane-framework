<?php

declare(strict_types=1);

namespace Horizon\Contracts\Support;

interface Jsonable
{
    public function toJson(): string;
}
