<?php

declare(strict_types=1);

namespace Tests\Dto;

use Horizon\Arch\Application;
use Horizon\Contracts\DTO\Casts\CastContract;
use Horizon\Contracts\DTO\DtoCollectionContract;
use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\DTO\DtoSerializerContract;
use Horizon\Dto\Attributes\CastWith;
use Horizon\Dto\Attributes\CollectionOf;
use Horizon\Dto\Attributes\MapFrom;
use Horizon\Dto\Attributes\MapTo;
use Horizon\Dto\Collections\DtoCollection;
use Horizon\Dto\DataTransferObject;
use Horizon\Dto\DtoFactory;
use Horizon\Dto\Exceptions\MissingDtoPropertyException;
use Horizon\Dto\Providers\DtoServiceProvider;
use PHPUnit\Framework\TestCase;

final class DtoTest extends TestCase
{
    public function test_factory_maps_constructor_dto_with_input_and_output_names(): void
    {
        $dto = (new DtoFactory)->make(UserSummaryDto::class, [
            'id' => 1,
            'full_name' => 'Ada Lovelace',
        ]);

        $this->assertInstanceOf(UserSummaryDto::class, $dto);
        $this->assertSame(1, $dto->id);
        $this->assertSame('Ada Lovelace', $dto->name);
        $this->assertSame([
            'id' => 1,
            'name' => 'Ada Lovelace',
            'email' => null,
        ], $dto->toArray());
    }

    public function test_static_constructor_maps_dto(): void
    {
        $dto = UserSummaryDto::from([
            'id' => 2,
            'full_name' => 'Grace Hopper',
        ]);

        $this->assertSame('Grace Hopper', $dto->name);
        $this->assertSame('{"id":2,"name":"Grace Hopper","email":null}', $dto->toJson());
    }

    public function test_mapper_fills_public_non_constructor_properties(): void
    {
        $dto = MutableUserDto::from([
            'id' => 3,
            'display_name' => 'Katherine Johnson',
        ]);

        $this->assertSame(3, $dto->id);
        $this->assertSame('Katherine Johnson', $dto->name);
        $this->assertSame([
            'id' => 3,
            'name' => 'Katherine Johnson',
        ], $dto->toArray());
    }

    public function test_nested_dto_and_collection_are_mapped_and_serialized(): void
    {
        $dto = TeamDto::from([
            'owner' => [
                'id' => 1,
                'full_name' => 'Ada',
            ],
            'posts' => [
                ['title' => 'First'],
                ['title' => 'Second'],
            ],
        ]);

        $this->assertInstanceOf(UserSummaryDto::class, $dto->owner);
        $this->assertInstanceOf(DtoCollection::class, $dto->posts);
        $this->assertInstanceOf(PostDto::class, $dto->posts->first());
        $this->assertSame('First', $dto->posts->first()->title);
        $this->assertSame([
            'owner' => [
                'id' => 1,
                'name' => 'Ada',
                'email' => null,
            ],
            'posts' => [
                ['title' => 'First'],
                ['title' => 'Second'],
            ],
        ], $dto->toArray());
    }

    public function test_casts_are_applied_when_mapping_and_serializing(): void
    {
        $dto = CastedNameDto::from(['name' => 'ada']);

        $this->assertSame('ADA', $dto->name);
        $this->assertSame(['name' => 'ada'], $dto->toArray());
    }

    public function test_missing_required_property_throws_exception(): void
    {
        $this->expectException(MissingDtoPropertyException::class);

        UserSummaryDto::from(['id' => 1]);
    }

    public function test_service_provider_registers_dto_bindings(): void
    {
        $app = new Application;
        $app->registerProvider(new DtoServiceProvider($app));

        $factory = $app->make(DtoFactoryContract::class);

        $this->assertInstanceOf(DtoFactoryContract::class, $factory);
        $this->assertInstanceOf(DtoSerializerContract::class, $app->make(DtoSerializerContract::class));
        $this->assertSame('Ada', $factory->make(UserSummaryDto::class, [
            'id' => 1,
            'full_name' => 'Ada',
        ])->name);
    }
}

final class UserSummaryDto extends DataTransferObject
{
    public function __construct(
        public int $id,
        #[MapFrom('full_name')]
        #[MapTo('name')]
        public string $name,
        public ?string $email = null,
    ) {}
}

final class MutableUserDto extends DataTransferObject
{
    #[MapFrom('display_name')]
    #[MapTo('name')]
    public string $name;

    public function __construct(
        public int $id,
    ) {}
}

final class TeamDto extends DataTransferObject
{
    public function __construct(
        public UserSummaryDto $owner,
        #[CollectionOf(PostDto::class)]
        public DtoCollectionContract $posts,
    ) {}
}

final class PostDto extends DataTransferObject
{
    public function __construct(
        public string $title,
    ) {}
}

final class CastedNameDto extends DataTransferObject
{
    public function __construct(
        #[CastWith(UppercaseNameCast::class)]
        public string $name,
    ) {}
}

final class UppercaseNameCast implements CastContract
{
    public function get(mixed $value): mixed
    {
        return strtoupper((string) $value);
    }

    public function set(mixed $value): mixed
    {
        return strtolower((string) $value);
    }
}
