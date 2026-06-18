<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Model\Traits;

use Horizon\Halcyon\Hydration\Casts\CarbonDateTimeCast;
use Horizon\Halcyon\Model\Attributes\Column;

trait HasSoftDeletes
{
    #[Column('deleted_at')]
    public ?CarbonDateTimeCast $deletedAt = null;
}
