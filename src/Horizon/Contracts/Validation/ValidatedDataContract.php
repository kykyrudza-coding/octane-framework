<?php

declare(strict_types=1);

namespace Horizon\Contracts\Validation;

use Horizon\Contracts\DTO\DtoContract;
use Horizon\Contracts\Support\Arrayable;
use Horizon\Contracts\Support\Jsonable;

interface ValidatedDataContract extends Arrayable, Jsonable
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array;

    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    public function except(array $keys): array;

    /**
     * @template TDto of DtoContract
     *
     * @param  class-string<TDto>  $dto
     * @return TDto
     */
    public function toDto(string $dto): DtoContract;
}
