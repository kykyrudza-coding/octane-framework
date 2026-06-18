<?php

declare(strict_types=1);

namespace Horizon\Dto\Metadata;

use Horizon\Contracts\DTO\Metadata\Cache\DtoMetadataCacheContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataParserContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataRepositoryContract;

class DtoMetadataRepository implements DtoMetadataRepositoryContract
{
    /**
     * @var array<class-string, DtoMetadataContract>
     */
    private array $metadata = [];

    private DtoMetadataParserContract $parser;

    public function __construct(
        ?DtoMetadataParserContract $parser = null,
        private readonly ?DtoMetadataCacheContract $cache = null,
    ) {
        $this->parser = $parser ?? new DtoMetadataParser;
    }

    public function for(string $class): DtoMetadataContract
    {
        if (isset($this->metadata[$class])) {
            return $this->metadata[$class];
        }

        $cached = $this->cache?->get($class);

        if ($cached !== null) {
            return $this->metadata[$class] = $cached;
        }

        $metadata = $this->parser()->parse($class);

        $this->metadata[$class] = $metadata;
        $this->cache?->put($class, $metadata);

        return $metadata;
    }

    public function flush(): void
    {
        $this->metadata = [];
        $this->cache?->flush();
    }

    private function parser(): DtoMetadataParserContract
    {
        return $this->parser;
    }
}
