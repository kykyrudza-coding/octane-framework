<?php

declare(strict_types=1);

namespace Horizon\Contracts\Support;

interface FluentContract
{
    public function get($key, $default = null): mixed;

    public function set($key, $value = null): FluentContract;
}
