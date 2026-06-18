# DTO

DTO пакет відповідає за перетворення масивів або об'єктів у typed data objects і назад у масиви/JSON.

Базовий DTO:

```php
use Horizon\Dto\DataTransferObject;

final class CreateUserDto extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
```

Створення:

```php
$dto = CreateUserDto::from([
    'name' => 'Ada',
    'email' => 'ada@example.com',
]);
```

Колекція:

```php
$users = CreateUserDto::collection($rows);
```

Серіалізація:

```php
$array = $dto->toArray();
$json = $dto->toJson();
```

Static API (`from`, `collection`, `toArray`) використовує container-bound `DtoFactoryContract` і `DtoSerializerContract`, якщо application уже завантажений. Без application context використовується локальна фабрика.

## Мапінг назв

`MapFrom` задає вхідне ім'я, `MapTo` - вихідне:

```php
use Horizon\Dto\Attributes\MapFrom;
use Horizon\Dto\Attributes\MapTo;

final class UserDto extends DataTransferObject
{
    public function __construct(
        #[MapFrom('user_name')]
        #[MapTo('name')]
        public string $name,
    ) {}
}
```

Nested DTO і collection items підтримуються через типи й `CollectionOf`:

```php
use Horizon\Dto\Attributes\CollectionOf;

final class TeamDto extends DataTransferObject
{
    public function __construct(
        public UserDto $owner,

        #[CollectionOf(UserDto::class)]
        public array $members,
    ) {}
}
```

## Casts

Attribute `CastWith` приймає клас, що реалізує `Horizon\Contracts\DTO\Casts\CastContract`:

```php
#[CastWith(DateTimeCast::class)]
public DateTimeImmutable $createdAt;
```

Cast застосовується під час mapping і serialization.

## Конфігурація

`config/dto.php`:

```php
return [
    'metadata' => [
        'cache' => [
            'enabled' => (bool) env('DTO_METADATA_CACHE', false),
        ],
    ],

    'mapping' => [
        'strict' => true,
        'unknown_fields' => 'ignore', // ignore або throw
        'missing_fields' => 'throw',  // throw або null
    ],

    'serialization' => [
        'include_null' => true,
        'json_flags' => JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
    ],
];
```

Активні ключі:

- `metadata.cache.enabled`: вмикає in-memory metadata cache repository;
- `mapping.strict`: коли `false`, missing required fields можуть бути заповнені `null`;
- `mapping.unknown_fields`: `throw` кидає `DtoMappingException` для зайвих top-level полів;
- `mapping.missing_fields`: `throw` або `null`;
- `serialization.include_null`: прибирає `null` значення з `toArray()`, якщо `false`;
- `serialization.json_flags`: flags для `toJson()`.

## DI API

```php
use Horizon\Contracts\DTO\DtoFactoryContract;

$factory = app(DtoFactoryContract::class);
$dto = $factory->make(CreateUserDto::class, $payload);
```

Основні контракти:

- `DtoFactoryContract`;
- `DtoMapperContract`;
- `DtoSerializerContract`;
- `DtoMetadataRepositoryContract`.
