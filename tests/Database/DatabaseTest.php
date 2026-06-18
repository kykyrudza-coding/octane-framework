<?php

declare(strict_types=1);

namespace Tests\Database;

use Horizon\Contracts\Database\Connections\ConnectionContract;
use Horizon\Contracts\Database\Connections\ConnectionFactoryContract;
use Horizon\Contracts\Database\Connections\ConnectionManagerContract;
use Horizon\Contracts\Database\Connections\Drivers\DriverContract;
use Horizon\Database\Connections\Connection;
use Horizon\Database\Connections\ConnectionManager;
use Horizon\Database\Migrations\Column;
use Horizon\Database\Migrations\CompositeIndex;
use Horizon\Database\Migrations\MigrationRepository;
use Horizon\Database\Schema\Compilers\SqliteSchemaCompiler;
use Horizon\Database\Schema\SchemaBuilder;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private Connection $connection;

    private ConnectionManagerContract $manager;

    protected function setUp(): void
    {
        $this->connection = new Connection($this->pdo(), 'default', 'sqlite');
        $this->manager = new DatabaseTestConnectionManager($this->connection);
    }

    public function test_connection_select_and_insert(): void
    {
        $this->connection->raw('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');

        $this->assertTrue($this->connection->insert('INSERT INTO users (name) VALUES (?)', ['Ada']));
        $this->assertSame('Ada', $this->connection->select('SELECT name FROM users')[0]['name']);
    }

    public function test_connection_update_returns_row_count(): void
    {
        $this->connection->raw('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $this->connection->insert('INSERT INTO users (name) VALUES (?)', ['Ada']);

        $this->assertSame(1, $this->connection->update('UPDATE users SET name = ? WHERE id = ?', ['Bob', 1]));
    }

    public function test_connection_delete_returns_row_count(): void
    {
        $this->connection->raw('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $this->connection->insert('INSERT INTO users (name) VALUES (?)', ['Ada']);

        $this->assertSame(1, $this->connection->delete('DELETE FROM users WHERE id = ?', [1]));
    }

    public function test_connection_transaction_commits(): void
    {
        $this->connection->raw('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');

        $this->connection->transaction(fn(Connection $connection) => $connection->insert('INSERT INTO users (name) VALUES (?)', ['Ada']));

        $this->assertSame(1, count($this->connection->select('SELECT * FROM users')));
    }

    public function test_connection_transaction_rolls_back(): void
    {
        $this->connection->raw('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');

        try {
            $this->connection->transaction(function (Connection $connection): void {
                $connection->insert('INSERT INTO users (name) VALUES (?)', ['Ada']);
                throw new \RuntimeException('fail');
            });
        } catch (\RuntimeException) {
            //
        }

        $this->assertSame([], $this->connection->select('SELECT * FROM users'));
    }

    public function test_connection_reports_name_and_driver(): void
    {
        $this->assertSame('default', $this->connection->getName());
        $this->assertSame('sqlite', $this->connection->getDriverName());
    }

    public function test_connection_query_log_can_be_enabled(): void
    {
        $this->connection->enableQueryLog();
        $this->connection->raw('CREATE TABLE users (id INTEGER)');

        $this->assertCount(1, $this->connection->getQueryLog());
        $this->assertSame('CREATE TABLE users (id INTEGER)', $this->connection->getQueryLog()[0]['query']);
    }

    public function test_connection_manager_returns_default_connection_once(): void
    {
        $factory = new DatabaseTestConnectionFactory($this->connection);
        $manager = new ConnectionManager($factory, [
            'default_connection' => 'sqlite',
            'connections' => ['sqlite' => ['driver' => 'sqlite']],
        ]);

        $this->assertSame($manager->connection(), $manager->connection());
        $this->assertSame(1, $factory->makeCalls);
    }

    public function test_connection_manager_reconnects_after_disconnect(): void
    {
        $factory = new DatabaseTestConnectionFactory($this->connection);
        $manager = new ConnectionManager($factory, [
            'default_connection' => 'sqlite',
            'connections' => ['sqlite' => ['driver' => 'sqlite']],
        ]);

        $manager->connection();
        $manager->reconnect();

        $this->assertSame(2, $factory->makeCalls);
    }

    public function test_connection_manager_rejects_missing_connection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ConnectionManager(new DatabaseTestConnectionFactory($this->connection), [
            'default_connection' => 'missing',
            'connections' => [],
        ]))->connection();
    }

    public function test_column_definition_tracks_modifiers(): void
    {
        $definition = Column::string('email')->unique()->nullable()->default('test@test')->toDefinition();

        $this->assertSame('string', $definition['type']);
        $this->assertTrue($definition['unique']);
        $this->assertTrue($definition['nullable']);
        $this->assertSame('test@test', $definition['default']);
    }

    public function test_composite_index_definition(): void
    {
        $definition = CompositeIndex::on(['tenant_id', 'email'])->unique()->toDefinition();

        $this->assertSame('composite_index', $definition['type']);
        $this->assertSame(['tenant_id', 'email'], $definition['columns']);
        $this->assertTrue($definition['unique']);
    }

    public function test_sqlite_compiler_creates_table_sql(): void
    {
        $sql = (new SqliteSchemaCompiler())->compileCreate('users', [
            Column::id(),
            Column::string('name'),
            Column::timestamps(),
            Column::softDeletes(),
        ]);

        $this->assertStringContainsString('CREATE TABLE "users"', $sql);
        $this->assertStringContainsString('"id" INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
        $this->assertStringContainsString('"created_at" TEXT NULL DEFAULT NULL', $sql);
        $this->assertStringContainsString('"deleted_at" TEXT NULL DEFAULT NULL', $sql);
    }

    public function test_schema_builder_create_has_column_rename_and_drop(): void
    {
        $schema = new SchemaBuilder($this->manager, new SqliteSchemaCompiler());
        $schema->create('users', [Column::id(), Column::string('name')]);

        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasColumn('users', 'name'));

        $schema->rename('users', 'members');
        $this->assertTrue($schema->hasTable('members'));

        $schema->dropIfExists('members');
        $this->assertFalse($schema->hasTable('members'));
    }

    public function test_schema_builder_places_foreign_keys_after_columns(): void
    {
        $schema = new SchemaBuilder($this->manager, new SqliteSchemaCompiler());
        $schema->create('users', [Column::id()]);

        $schema->create('posts', [
            Column::id(),
            Column::foreignId('user_id')->references('id')->on('users')->cascadeOnDelete(),
            Column::string('title'),
            Column::text('description'),
            Column::timestamps(),
        ]);

        $this->assertTrue($schema->hasTable('posts'));
        $this->assertTrue($schema->hasColumn('posts', 'title'));
    }

    public function test_migration_repository_creates_table_and_stores_records(): void
    {
        $repository = new MigrationRepository($this->manager);
        $repository->createTable();
        $repository->store('2026_create_users.php', 1);
        $repository->store('2026_create_posts.php', 2);

        $this->assertTrue($repository->tableExists());
        $this->assertSame(['2026_create_users.php', '2026_create_posts.php'], $repository->getRan());
        $this->assertSame(2, $repository->getLastBatch());
        $this->assertSame(1, $repository->getBatch('2026_create_users.php'));
    }

    public function test_migration_repository_pending_and_delete(): void
    {
        $repository = new MigrationRepository($this->manager);
        $repository->createTable();
        $repository->store('ran.php', 1);

        $this->assertSame(['pending.php'], $repository->getPending(['ran.php', 'pending.php']));

        $repository->delete('ran.php');
        $this->assertSame([], $repository->getRan());
    }

    private function pdo(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}

final class DatabaseTestConnectionManager implements ConnectionManagerContract
{
    public function __construct(private ConnectionContract $connection) {}

    public function connection(string $name = 'default'): ConnectionContract
    {
        return $this->connection;
    }

    public function extend(string $driver, string $driverClass): void {}

    public function reconnect(string $name = 'default'): ConnectionContract
    {
        return $this->connection;
    }

    public function disconnect(string $name = 'default'): void {}
}

final class DatabaseTestConnectionFactory implements ConnectionFactoryContract
{
    public int $makeCalls = 0;

    public function __construct(private ConnectionContract $connection) {}

    public function make(string $name, array $config): ConnectionContract
    {
        $this->makeCalls++;

        return $this->connection;
    }

    public function extend(string $driverName, DriverContract $driver): void {}
}
