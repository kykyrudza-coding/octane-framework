<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Metadata\Cache;

use Horizon\Contracts\Halcyon\Metadata\Cache\MetadataCacheContract;
use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;
use Horizon\Halcyon\Exceptions\MetadataCacheException;
use Throwable;

final readonly class FileMetadataCache implements MetadataCacheContract
{
    public function __construct(
        private string $cachePath,
    ) {}

    public function has(string $class): bool
    {
        return file_exists($this->resolvePath($class));
    }

    public function get(string $class): ?ModelMetadataContract
    {
        $path = $this->resolvePath($class);

        if (! file_exists($path)) {
            return null;
        }

        try {
            $metadata = unserialize(file_get_contents($path));
        } catch (Throwable $e) {
            throw MetadataCacheException::readFailed($class, $e);
        }

        if (! $metadata instanceof ModelMetadataContract) {
            return null;
        }

        return $metadata;
    }

    public function set(string $class, ModelMetadataContract $metadata): void
    {
        $path = $this->resolvePath($class);

        $this->ensureDirectoryExists();

        try {
            file_put_contents($path, serialize($metadata));
        } catch (Throwable $e) {
            throw MetadataCacheException::writeFailed($class, $e);
        }
    }

    public function forget(string $class): void
    {
        $path = $this->resolvePath($class);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function flush(): void
    {
        $files = glob($this->cachePath . DIRECTORY_SEPARATOR . '*.cache');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            unlink($file);
        }
    }

    private function resolvePath(string $class): string
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . $this->resolveKey($class) . '.cache';
    }

    private function resolveKey(string $class): string
    {
        return str_replace('\\', '_', $class);
    }

    private function ensureDirectoryExists(): void
    {
        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, recursive: true);
        }
    }
}
