<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Model\Traits;

use Horizon\Halcyon\Hydration\Casts\CarbonDateTimeCast;
use Horizon\Halcyon\Model\Attributes\Column;

trait HasTimestamps
{

    #[Column('created_at')]
    public CarbonDateTimeCast $createdAt;

    #[Column('updated_at')]
    public CarbonDateTimeCast $updatedAt;
}
