<?php

declare(strict_types=1);

namespace Horizon\Halcyon\Metadata;

use Horizon\Contracts\Halcyon\Metadata\Cache\MetadataCacheContract;
use Horizon\Contracts\Halcyon\Metadata\MetadataParserContract;
use Horizon\Contracts\Halcyon\Metadata\MetadataRepositoryContract;
use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;

final readonly class MetadataRepository implements MetadataRepositoryContract
{
    public function __construct(
        private MetadataParserContract $parser,
        private ?MetadataCacheContract $cache,
        private bool $cacheEnabled = true,
    ) {}

    public function for(string $class): ModelMetadataContract
    {
        if (! $this->cacheEnabled || $this->cache === null) {
            return $this->parser->parse($class);
        }

        if ($this->cache->has($class)) {
            return $this->cache->get($class);
        }

        $metadata = $this->parser->parse($class);

        $this->cache->set($class, $metadata);

        return $metadata;
    }

    public function flush(): void
    {
        if (! $this->cacheEnabled || $this->cache === null) {
            return;
        }

        $this->cache->flush();
    }
}
