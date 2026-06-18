<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Exceptions;

use RuntimeException;

final class ModelMetadataException extends RuntimeException
{
    public static function missingTableAttribute(string $class): ModelMetadataException
    {
        return new ModelMetadataException("Model [$class] is missing the #[Table] attribute.");
    }

    public static function invalidModel(string $class): ModelMetadataException
    {
        return new ModelMetadataException("Class [$class] is not a valid Halcyon model.");
    }
}
