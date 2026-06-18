<?php

declare(strict_types=1);

namespace Tests\Halcyon;

use Horizon\Arch\Application;
use Horizon\Contracts\Halcyon\Hydration\Casts\CastContract;
use Horizon\Contracts\Halcyon\Metadata\MetadataParserContract;
use Horizon\Contracts\Halcyon\Metadata\ModelMetadataContract;
use Horizon\Contracts\Halcyon\OrmConfiguratorContract;
use Horizon\Halcyon\Exceptions\MissingTableAttributeException;
use Horizon\Halcyon\Exceptions\RelationNotLoadedException;
use Horizon\Halcyon\Halcyon;
use Horizon\Halcyon\Hydration\Hydrator;
use Horizon\Halcyon\Metadata\Cache\FileMetadataCache;
use Horizon\Halcyon\Metadata\MetadataParser;
use Horizon\Halcyon\Metadata\MetadataRepository;
use Horizon\Halcyon\Model\Attributes\Column;
use Horizon\Halcyon\Model\Attributes\Table;
use Horizon\Halcyon\Model\Model;
use Horizon\Halcyon\Providers\HalcyonServiceProvider;
use Horizon\Halcyon\Relations\BelongsToMany;
use Horizon\Halcyon\Relations\HasMany;
use Horizon\Halcyon\Relations\Relation;
use Horizon\Support\CarbonTimestamp;
use Horizon\Support\Facades\Halcyon as HalcyonFacade;
use Horizon\Support\ItemsList;
use PHPUnit\Framework\TestCase;

final class HalcyonTest extends TestCase
{
    public function test_metadata_parser_reads_table_properties_hooks_and_relations(): void
    {
        $metadata = (new MetadataParser)->parse(HalcyonUserFixture::class);

        $this->assertSame(HalcyonUserFixture::class, $metadata->getClass());
        $this->assertSame('users', $metadata->getTable());
        $this->assertArrayHasKey('id', $metadata->getProperties());
        $this->assertArrayHasKey('createdAt', $metadata->getProperties());
        $this->assertSame('created_at', $metadata->getProperties()['createdAt']->getColumnName());
        $this->assertTrue($metadata->getProperties()['createdAt']->isNullable());
        $this->assertSame(['password'], $metadata->getHidden());
        $this->assertSame(['name' => UppercaseCastFixture::class], $metadata->getCasts());
        $this->assertSame([HalcyonObserverFixture::class], $metadata->getObservers());
        $this->assertSame([HalcyonScopeFixture::class], $metadata->getScopes());

        $this->assertArrayHasKey('posts', $metadata->getRelations());
        $this->assertSame('posts', $metadata->getRelations()['posts']->getName());
        $this->assertSame(HalcyonPostFixture::class, $metadata->getRelations()['posts']->getRelated());
        $this->assertSame('user_id', $metadata->getRelations()['posts']->getForeignKey());

        $this->assertArrayHasKey('roles', $metadata->getRelations());
        $this->assertSame('role_user', $metadata->getRelations()['roles']->getPivotTable());
    }

    public function test_metadata_parser_requires_table_attribute(): void
    {
        $this->expectException(MissingTableAttributeException::class);

        (new MetadataParser)->parse(HalcyonModelWithoutTableFixture::class);
    }

    public function test_metadata_repository_uses_file_cache_when_enabled(): void
    {
        $cachePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'octane_halcyon_test_'.bin2hex(random_bytes(4));
        $parser = new CountingMetadataParserFixture;
        $repository = new MetadataRepository(
            parser: $parser,
            cache: new FileMetadataCache($cachePath),
            cacheEnabled: true,
        );

        $first = $repository->for(HalcyonUserFixture::class);
        $second = $repository->for(HalcyonUserFixture::class);

        $this->assertSame(1, $parser->count);
        $this->assertEquals($first, $second);

        $repository->flush();
    }

    public function test_hydrator_returns_items_list_of_models_and_applies_casts(): void
    {
        $metadata = (new MetadataParser)->parse(HalcyonUserFixture::class);
        $items = (new Hydrator)->hydrate($metadata, [
            [
                'id' => 1,
                'name' => 'test',
                'email' => 'test@example.com',
                'password' => 'secret',
                'created_at' => '2026-06-18 10:11:12',
            ],
        ]);

        $this->assertInstanceOf(ItemsList::class, $items);

        /** @var HalcyonUserFixture $user */
        $user = $items->first();

        $this->assertInstanceOf(HalcyonUserFixture::class, $user);
        $this->assertSame(1, $user->id);
        $this->assertSame('TEST', $user->name);
        $this->assertInstanceOf(CarbonTimestamp::class, $user->createdAt);
        $this->assertSame('2026-06-18 10:11:12', $user->createdAt->toDateTimeString());
    }

    public function test_model_relation_access_requires_loaded_relation(): void
    {
        $model = new HalcyonUserFixture;

        $this->assertFalse($model->relationLoaded('posts'));
        $this->expectException(RelationNotLoadedException::class);

        $model->getRelation('posts');
    }

    public function test_halcyon_registry_tracks_observers_scopes_and_morph_map(): void
    {
        $halcyon = new Halcyon;

        $halcyon->observe(HalcyonUserFixture::class, HalcyonObserverFixture::class);
        $halcyon->scope(HalcyonUserFixture::class, HalcyonScopeFixture::class);
        $halcyon->morphMap(['user' => HalcyonUserFixture::class]);

        $this->assertSame([HalcyonObserverFixture::class], $halcyon->getObservers()[HalcyonUserFixture::class]);
        $this->assertSame([HalcyonScopeFixture::class], $halcyon->getScopes()[HalcyonUserFixture::class]);
        $this->assertSame(['user' => HalcyonUserFixture::class], $halcyon->getMorphMap());
    }

    public function test_halcyon_facade_configures_orm_registry_through_configurator_contract(): void
    {
        $app = new Application;
        $app->registerProvider(new HalcyonServiceProvider($app));

        HalcyonFacade::observe(HalcyonUserFixture::class, HalcyonObserverFixture::class);
        HalcyonFacade::scope(HalcyonUserFixture::class, HalcyonScopeFixture::class);
        HalcyonFacade::morphMap(['user' => HalcyonUserFixture::class]);

        $halcyon = $app->make(OrmConfiguratorContract::class);

        $this->assertSame([HalcyonObserverFixture::class], $halcyon->getObservers()[HalcyonUserFixture::class]);
        $this->assertSame([HalcyonScopeFixture::class], $halcyon->getScopes()[HalcyonUserFixture::class]);
        $this->assertSame(['user' => HalcyonUserFixture::class], $halcyon->getMorphMap());
    }
}

#[Table('users')]
final class HalcyonUserFixture extends Model
{
    public int $id;

    public string $name;

    public string $email;

    public string $password;

    #[Column('created_at')]
    public ?CarbonTimestamp $createdAt = null;

    protected static function hidden(): array
    {
        return ['password'];
    }

    protected static function casts(): array
    {
        return ['name' => UppercaseCastFixture::class];
    }

    protected static function observers(): array
    {
        return [HalcyonObserverFixture::class];
    }

    protected static function scopes(): array
    {
        return [HalcyonScopeFixture::class];
    }

    protected function posts(): HasMany
    {
        return Relation::hasMany(
            related: HalcyonPostFixture::class,
            foreignKey: 'user_id',
            localKey: 'id',
        );
    }

    protected function roles(): BelongsToMany
    {
        return Relation::belongsToMany(
            related: HalcyonRoleFixture::class,
            pivotTable: 'role_user',
            foreignKey: 'user_id',
            localKey: 'id',
        );
    }
}

#[Table('posts')]
final class HalcyonPostFixture extends Model
{
    public int $id;
}

#[Table('roles')]
final class HalcyonRoleFixture extends Model
{
    public int $id;
}

final class HalcyonModelWithoutTableFixture extends Model
{
    public int $id;
}

final class UppercaseCastFixture implements CastContract
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

final class CountingMetadataParserFixture implements MetadataParserContract
{
    public int $count = 0;

    public function parse(string $class): ModelMetadataContract
    {
        $this->count++;

        return (new MetadataParser)->parse($class);
    }
}

final class HalcyonObserverFixture {}

final class HalcyonScopeFixture {}
