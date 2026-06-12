<?php

declare(strict_types=1);

namespace Horizon\Support\Enums;

enum Environment: string
{
    case PRODUCTION = 'production';
    case DEVELOPMENT = 'development';
    case LOCAL = 'local';
    case TESTING = 'testing';
}

