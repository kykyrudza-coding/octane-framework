# Halcyon and QueryBuilder Integration

## Goal

Keep the boundary strict:

- QueryBuilder builds SQL and executes queries.
- Halcyon parses model metadata and hydrates rows into models.
- Models remain Data Mapper entities, not Active Record objects.

## Desired Public Behavior

```php
QueryBuilder::table('users')->get();
```

Returns raw database rows in `ItemsList`.

```php
QueryBuilder::for(User::class)->get();
```

Returns `ItemsList<User>`.

```php
QueryBuilder::for(User::class)->first();
```

Returns `?User`.

## Implemented QueryBuilder Changes

1. QueryBuilder stores model class separately from table name.
2. `for(Model::class)` asks `QueryResultMapperContract::tableFor()` for the table name.
3. `table()` clears model target and stays raw.
4. `get()` executes SQL into `ItemsList<QueryRow>` first, then maps rows through `QueryResultMapperContract`.
5. `first()` returns the first mapped result, so model queries return `?Model`.

## Implemented Halcyon Changes

1. Added `Horizon\Halcyon\Query\HalcyonResultMapper`.
2. `HalcyonServiceProvider` registers it as `QueryResultMapperContract`.
3. Mapper converts `QueryRow` objects to arrays before hydration.
4. Hydration preserves `ItemsList` as the collection wrapper.

## Runtime Flow

```text
QueryBuilder::for(User::class)
  -> QueryResultMapperContract::tableFor(User::class)
  -> Halcyon metadata repository reads #[Table]
  -> QueryBuilder executes SQL
  -> raw rows become ItemsList<QueryRow>
  -> HalcyonResultMapper hydrates rows
  -> ItemsList<User>
```

## Later Relation Loading

Eager loading can later be implemented as:

```php
QueryBuilder::for(User::class)->with('posts')->get();
```

That requires relation metadata, secondary QueryBuilder queries, and assignment through `Model::setRelation()`.

## Non-Goals

- No `User::query()`.
- No `$user->save()`.
- No model-level persistence API.
- No lazy loading on property access.
