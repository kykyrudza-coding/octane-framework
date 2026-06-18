# Halcyon ORM

## Philosophy

Halcyon is not Active Record.

Model is not:

- query API
- repository
- DTO
- service
- persistence object with `save()` / `delete()`

Model is:

- database entity metadata
- typed field definitions
- relation definitions
- hydration target

All database interaction goes through QueryBuilder.

Forbidden model API:

```php
User::where(...);
User::query();
$user->save();
$user->delete();
$user->update();
```

Accepted query flow:

```text
QueryBuilder
  -> Halcyon metadata
  -> Database
  -> Halcyon hydrator
  -> Model objects
```

## Model Properties

Use regular PHP typed properties:

```php
#[Table('users')]
class User extends Model
{
    use HasTimestamps;
    use HasSoftDeletes;

    public int $id;
    public string $name;
    public string $email;
    public string $password;

    #[Column('created_at')]
    public CarbonTimestamp $createdAt;

    #[Column('deleted_at')]
    public ?CarbonTimestamp $deletedAt = null;
}
```

Do not use cast wrapper properties like `StringCast $name` as the default model syntax.

## Hidden, Visible, Guarded

`hidden()` remains in the model for safety so hidden fields do not leak into views/resources.

`visible()` belongs in ApiResource, not model.

`guarded()` is not accepted right now. Do not document it as a final model API. It may return later after mass-assignment rules are clearer.

Password hashing is not guarded. It belongs to a casts/mutators layer. Exact implementation is still open.

## Casts

Default casting should come from PHP type hints.

Custom casts should be supported through a method/registry mechanism.

Attributes like `#[Cast(...)]` can exist later, but they are not the primary accepted model cast surface right now.

## Relations

Relations are model methods returning relation objects.

Accepted style:

```php
protected function posts(): HasMany
{
    return Relation::hasMany(
        related: Post::class,
        foreignKey: 'user_id',
        localKey: 'id',
    );
}
```

Rejected styles:

```php
$this->hasMany(...);
```

```php
#[HasMany]
public array $posts;
```

## Observers

Support both:

- model-level registration, such as `protected static function observers(): array`
- provider/facade registration, such as `Halcyon::observe(User::class, UserObserver::class)`

Provider/facade registration is important for global configuration and keeping models smaller in larger apps.

Observer example:

```php
final class UserObserver
{
    public function creating(User $user): void {}
    public function created(User $user): void {}
    public function updating(User $user): void {}
    public function updated(User $user): void {}
    public function deleting(User $user): void {}
    public function deleted(User $user): void {}
}
```

## Scopes

Support both:

- model-level scopes
- global/provider-level scopes

Global scopes for all models are needed, with ability to disable per query. Example use case: multi-application filtering by active app id.

## ModelDefinition

Do not introduce `UserDefinition` / `ModelDefinition` classes now.

They are considered over-abstraction for the current DX and should be treated as rejected unless future requirements force the idea back.

## Halcyon Facade

Halcyon facade is for ORM configuration, not queries.

Accepted surface:

```php
Halcyon::observe(User::class, UserObserver::class);
Halcyon::scope(User::class, ActiveScope::class);
Halcyon::morphMap([
    'user' => User::class,
    'post' => Post::class,
]);
```

Queries belong to QueryBuilder.

## Metadata Cache

Model metadata cache must be separate from database metadata cache.

Accepted config key spelling:

```php
'halcyon' => [
    'metadata' => [
        'cache' => [
            'enabled' => true,
        ],
    ],
],
```

The user specifically prefers `halcyon.metadata.cache`, not `halcyon.metadata_cache`.

In development, model metadata cache should be easy to disable.

## Implementation Update

Halcyon is now present as an implemented framework module, but it is still a mapper/hydration layer, not a query API.

Implemented surfaces:

- `Horizon\Halcyon\Model\Model` as the base model with loaded relation storage.
- `#[Table]` and `#[Column]` attributes for table and column mapping.
- `MetadataParser`, `MetadataRepository`, `ModelMetadata`, `PropertyMetadata`, and `RelationMetadata`.
- File-backed metadata cache through `FileMetadataCache`.
- `Hydrator` for row-array to model-object hydration.
- Built-in casts for `CarbonTimestamp`, `CarbonDateTime`, and backed enums.
- Relation descriptors: `HasMany`, `HasOne`, `BelongsTo`, `BelongsToMany`.
- `Halcyon` registry service and facade for observers, scopes, and morph map configuration.

Current important rules:

- Model methods such as `hidden()`, `casts()`, `observers()`, and `scopes()` may stay protected static methods. The metadata parser reads them through reflection.
- Relation methods are protected instance methods returning relation objects. The method name is the relation name when the relation object does not define an explicit name.
- `Relation::hasMany(...)`, `hasOne(...)`, `belongsTo(...)`, and `belongsToMany(...)` support named arguments without forcing a `name` argument.
- `Hydrator::hydrate()` returns `ItemsList<Model>`.
- `MetadataRepository` caches parsed `ModelMetadata` only when metadata cache is enabled and a cache backend exists.
- Halcyon does not lazy-load relations yet. Accessing an unloaded relation throws `RelationNotLoadedException`.

Implemented integration boundary:

- QueryBuilder owns SQL and raw row retrieval.
- Halcyon owns model metadata and hydration.
- `QueryBuilder::table('users')` keeps returning raw rows.
- `QueryBuilder::for(User::class)` resolves Halcyon metadata, selects the metadata table, executes the query, and hydrates rows into `ItemsList<User>`.
- The boundary is `QueryResultMapperContract`; QueryBuilder does not depend directly on Halcyon classes.
