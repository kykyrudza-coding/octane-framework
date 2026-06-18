<?php

declare(strict_types=1);

namespace Horizon\Dto;

use Horizon\Contracts\DTO\DtoCollectionContract;
use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\DTO\DtoMapperContract;
use Horizon\Contracts\DTO\Metadata\DtoMetadataRepositoryContract;
use Horizon\Dto\Collections\DtoCollection;
use Horizon\Dto\Metadata\DtoMetadataRepository;

class DtoFactory implements DtoFactoryContract
{
    private DtoMetadataRepositoryContract $metadata;

    private DtoMapperContract $mapper;

    public function __construct(
        ?DtoMetadataRepositoryContract $metadata = null,
        ?DtoMapperContract $mapper = null,
    ) {
        $this->metadata = $metadata ?? new DtoMetadataRepository;
        $this->mapper = $mapper ?? new DtoMapper($this->metadata);
    }

    public function make(string $dto, object|array $data): DtoContract
    {
        return $this->mapper()
            ->map($this->metadata()->for($dto), $data);
    }

    public function collection(string $dto, iterable $items): DtoCollectionContract
    {

        $mapped = array_map(function ($item) use ($dto) {
            return $item instanceof DtoContract
                ? $item
                : $this->make($dto, $item);
        }, (array)$items);

        return new DtoCollection($mapped);
    }

    private function metadata(): DtoMetadataRepositoryContract
    {
        return $this->metadata;
    }

    private function mapper(): DtoMapperContract
    {
        return $this->mapper;
    }
}
