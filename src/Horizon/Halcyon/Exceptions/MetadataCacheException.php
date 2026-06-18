<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;
use Throwable;

final class MetadataCacheException extends RuntimeException
{
    public static function readFailed(string $class, Throwable $previous): MetadataCacheException
    {
        return new MetadataCacheException(
            "Failed to read metadata cache for model [$class].",
            previous: $previous
        );
    }

    public static function writeFailed(string $class, Throwable $previous): MetadataCacheException
    {
        return new MetadataCacheException(
            "Failed to write metadata cache for model [$class].",
            previous: $previous
        );
    }
}
