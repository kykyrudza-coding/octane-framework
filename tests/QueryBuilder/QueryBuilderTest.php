<?php

declare(strict_types=1);

namespace Tests\QueryBuilder;

use Horizon\QueryBuilder\Exceptions\QueryBuilderException;
use Horizon\Halcyon\Hydration\Hydrator;
use Horizon\Halcyon\Metadata\MetadataParser;
use Horizon\Halcyon\Metadata\MetadataRepository;
use Horizon\Halcyon\Model\Attributes\Column;
use Horizon\Halcyon\Model\Attributes\Table;
use Horizon\Halcyon\Model\Model;
use Horizon\Halcyon\Query\HalcyonResultMapper;
use Horizon\QueryBuilder\QueryBuilder;
use Horizon\QueryBuilder\QueryBuilderFactory;
use Horizon\QueryBuilder\Results\QueryRow;
use Horizon\Support\ItemsList;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use stdClass;

class QueryBuilderTest extends TestCase
{
    private PDO $pdo;

    private QueryBuilderFactory $factory;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, active INTEGER, created_at TEXT NULL)');
        $this->pdo->exec("INSERT INTO users (name, email, active, created_at) VALUES ('Ada', 'ada@test', 1, '2026-01-01')");
        $this->pdo->exec("INSERT INTO users (name, email, active, created_at) VALUES ('Bob', 'bob@test', 0, '2026-01-02')");
        $this->pdo->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT)');
        $this->pdo->exec("INSERT INTO posts (user_id, title) VALUES (1, 'Hello')");

        $this->factory = new QueryBuilderFactory($this->pdo, 'sqlite');
    }

    public function test_factory_creates_fresh_builder_instances(): void
    {
        $this->assertNotSame($this->factory->make(), $this->factory->make());
    }

    public function test_factory_targets_table(): void
    {
        $sql = $this->factory->forTable('users')->toSql();

        $this->assertSame('SELECT * FROM "users"', $sql);
    }

    public function test_factory_targets_model_by_convention(): void
    {
        $builder = $this->factory->forModel(QueryBuilderUserProfile::class);

        $this->assertSame('SELECT * FROM "query_builder_user_profiles"', $builder->toSql());
    }

    public function test_factory_rejects_unknown_driver(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new QueryBuilderFactory($this->pdo, 'oracle'))->make();
    }

    public function test_get_returns_items_list_of_query_rows(): void
    {
        $users = $this->factory->forTable('users')->get();

        $this->assertInstanceOf(ItemsList::class, $users);
        $this->assertInstanceOf(QueryRow::class, $users->first());
        $this->assertNotInstanceOf(stdClass::class, $users->first());
    }

    public function test_table_query_stays_raw_when_halcyon_mapper_is_available(): void
    {
        $factory = new QueryBuilderFactory($this->pdo, 'sqlite', $this->halcyonMapper());

        $user = $factory->forTable('users')->where('id', '=', 1)->first();

        $this->assertInstanceOf(QueryRow::class, $user);
        $this->assertSame('Ada', $user->name);
    }

    public function test_for_model_uses_halcyon_metadata_and_hydrates_models(): void
    {
        $factory = new QueryBuilderFactory($this->pdo, 'sqlite', $this->halcyonMapper());

        $users = $factory->forModel(QueryBuilderHalcyonUser::class)
            ->where('active', '=', 1)
            ->get();

        $this->assertInstanceOf(ItemsList::class, $users);
        $this->assertCount(1, $users);
        $this->assertInstanceOf(QueryBuilderHalcyonUser::class, $users->first());
        $this->assertSame('Ada', $users->first()->name);
        $this->assertSame('2026-01-01', $users->first()->createdAt);
    }

    public function test_first_for_model_returns_single_hydrated_model(): void
    {
        $factory = new QueryBuilderFactory($this->pdo, 'sqlite', $this->halcyonMapper());

        $user = $factory->forModel(QueryBuilderHalcyonUser::class)
            ->where('id', '=', 2)
            ->first();

        $this->assertInstanceOf(QueryBuilderHalcyonUser::class, $user);
        $this->assertSame('Bob', $user->name);
    }

    public function test_query_row_supports_property_array_and_get_access(): void
    {
        $user = $this->factory->forTable('users')->where('id', '=', 1)->first();

        $this->assertInstanceOf(QueryRow::class, $user);
        $this->assertSame('Ada', $user->name);
        $this->assertSame('ada@test', $user['email']);
        $this->assertSame(1, $user->get('id'));
        $this->assertTrue(isset($user->name));
    }

    public function test_query_row_converts_to_array_and_json(): void
    {
        $user = $this->factory->forTable('users')->where('id', '=', 1)->first();

        $this->assertSame('Ada', $user->toArray()['name']);
        $this->assertJson($user->toJson());
    }

    public function test_select_limits_columns(): void
    {
        $user = $this->factory->forTable('users')->select('name')->where('id', '=', 1)->first();

        $this->assertSame(['name' => 'Ada'], $user->toArray());
    }

    public function test_where_filters_rows(): void
    {
        $users = $this->factory->forTable('users')->where('active', '=', 1)->get();

        $this->assertCount(1, $users);
        $this->assertSame('Ada', $users->first()->name);
    }

    public function test_or_where_filters_rows(): void
    {
        $users = $this->factory->forTable('users')
            ->where('name', '=', 'Ada')
            ->orWhere('name', '=', 'Bob')
            ->get();

        $this->assertCount(2, $users);
    }

    public function test_where_raw_uses_bindings(): void
    {
        $users = $this->factory->forTable('users')->whereRaw('active = ?', [0])->get();

        $this->assertCount(1, $users);
        $this->assertSame('Bob', $users->first()->name);
    }

    public function test_order_by_and_limit_apply_to_query(): void
    {
        $user = $this->factory->forTable('users')->orderByDesc('id')->limit(1)->first();

        $this->assertSame('Bob', $user->name);
    }

    public function test_offset_skips_rows(): void
    {
        $user = $this->factory->forTable('users')->orderBy('id')->limit(1)->offset(1)->first();

        $this->assertSame('Bob', $user->name);
    }

    public function test_join_reads_related_rows(): void
    {
        $row = $this->factory->forTable('users')
            ->select('users.name', 'posts.title')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->first();

        $this->assertSame('Ada', $row->name);
        $this->assertSame('Hello', $row->title);
    }

    public function test_count_and_exists(): void
    {
        $builder = $this->factory->forTable('users')->where('active', '=', 1);

        $this->assertSame(1, $builder->count());
        $this->assertTrue($builder->exists());
        $this->assertFalse($this->factory->forTable('users')->where('name', '=', 'Missing')->exists());
    }

    public function test_insert_create_update_and_delete(): void
    {
        $this->assertTrue($this->factory->forTable('users')->insert([
            'name' => 'Chris',
            'email' => 'chris@test',
            'active' => 1,
        ]));

        $id = $this->factory->forTable('users')->create([
            'name' => 'Dana',
            'email' => 'dana@test',
            'active' => 1,
        ]);

        $this->assertNotFalse($id);
        $this->assertSame(1, $this->factory->forTable('users')->where('name', '=', 'Dana')->update(['active' => 0]));
        $this->assertSame(1, $this->factory->forTable('users')->where('name', '=', 'Chris')->delete());
    }

    public function test_to_sql_and_bindings_are_available(): void
    {
        $builder = $this->factory->forTable('users')->where('name', '=', 'Ada')->orderBy('id')->limit(5);

        $this->assertSame('SELECT * FROM "users" WHERE "name" = ? ORDER BY "id" ASC LIMIT 5', $builder->toSql());
        $this->assertSame(['Ada'], $builder->getBindings());
    }

    public function test_missing_table_throws_exception(): void
    {
        $this->expectException(QueryBuilderException::class);

        $this->factory->make()->get();
    }

    private function halcyonMapper(): HalcyonResultMapper
    {
        return new HalcyonResultMapper(
            metadata: new MetadataRepository(
                parser: new MetadataParser(),
                cache: null,
                cacheEnabled: false,
            ),
            hydrator: new Hydrator(),
        );
    }
}

final class QueryBuilderUserProfile {}

#[Table('users')]
final class QueryBuilderHalcyonUser extends Model
{
    public int $id;

    public string $name;

    public string $email;

    public int $active;

    #[Column('created_at')]
    public ?string $createdAt = null;
}
