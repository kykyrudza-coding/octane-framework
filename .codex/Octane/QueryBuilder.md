# QueryBuilder

## Priority

QueryBuilder is the next major implementation target.

Database exists with connections, migrations, seeders, schema pieces. Halcyon depends on QueryBuilder, so QueryBuilder comes before Halcyon.

## Facade

The facade should be named `QueryBuilder`.

`DB` is reserved for low-level database operations:

- raw SQL execution
- connection access
- transactions
- select/insert/update/delete at connection level

Do not make `DB` a shortcut facade for QueryBuilder.

## Public API

Support both:

```php
QueryBuilder::table('users')
```

and:

```php
QueryBuilder::for(User::class)
```

`for(Model::class)` can temporarily map class names to table-name conventions before Halcyon metadata exists.

## Result Type

`get()` should return an `ItemsList`-like collection object. `ItemsList` is conceptually similar to a Laravel Collection, but should be Octane's own lightweight list abstraction.

Do not make raw arrays the long-term default result surface.

## Internal Structure

Prefer separate builders:

- `SelectBuilder`
- `InsertBuilder`
- `UpdateBuilder`
- `DeleteBuilder`

Expose them through a factory/facade surface so user code remains ergonomic.

Current first milestone should start with:

1. contracts
2. service provider
3. factory
4. facade
5. builder skeletons
6. grammar selection

Then add query operations.

## Grammar

Use grammar classes with contracts.

Target grammars:

- MySQL
- PostgreSQL
- SQLite

Do not hardcode only MySQL as the architecture.

## Mutations

QueryBuilder should include create/update/delete APIs from the first real version.

Mutation APIs can internally use separate builders.

## Raw SQL

Raw low-level execution belongs to `DB`.

QueryBuilder should still support raw expressions in builder context:

- `selectRaw`
- `whereRaw`
- raw expression objects

This should be implemented carefully through expression/grammar objects rather than arbitrary string concatenation everywhere.

## Config

Current application draft includes `config/query-builder.php` with:

- default connection
- fetch mode
- pagination per-page
- debug query logging

Keep query-builder config separate from database config when it concerns builder behavior.
