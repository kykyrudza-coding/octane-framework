# Console, Database, Support

## Console Commands

`CommandRegistry` must not instantiate every command when registering command classes. Some commands depend on database services, which can open connections too early.

Accepted design:

- `CommandContract` includes `public static function commandName(): string`
- `Command::name()` can delegate to `static::commandName()`
- `CommandRegistry::register()` stores class names by static command name
- command instances are resolved only when invoked

This prevents `php octane list` from opening database connections just to list commands.

## Console UI

Keep richer console UI utilities inside `Console`, not `Support`.

Accepted utilities:

- ANSI styles
- `OutputFormatter`
- `OutputStyle`
- `Table`
- `ProgressBar`
- `Spinner`

Modern CLI output is expected to be polished, but implementation should remain pragmatic and testable.

## Database

Database package/layer owns:

- connection factory
- connection manager
- drivers
- migrations
- schema builder/compiler
- seeders
- low-level DB facade

`DB` facade is low-level, not QueryBuilder.

SQLite relative paths should resolve through application database path, not become absolute root paths like `/db/database.sqlite`.

Database connections should be lazy. Avoid opening PDO connections while registering/listing commands.

## Schema And Migration Contracts

Contracts should match concrete methods.

Known debt:

- schema compiler contract must declare compile methods used by `SchemaBuilder`
- connection contracts should include query-log methods if code uses them
- factory contract should match `extend()` behavior if manager exposes driver extension

## Support

Support contains shared low-level utilities:

- facades
- helpers
- hashing
- casts registry
- value objects
- traits
- pagination
- benchmark
- common exceptions

Do not put business/domain framework modules into Support.

## Hashing

Support hashing direction:

- `HasherContract`
- `BcryptHasher`
- `Argon2Hasher`
- configured through `SupportServiceProvider`

Hashing can later be used by model casts/mutators, auth, validation, etc.

## Traits

Current support traits:

- `Conditionable`
- `Tappable`
- `Singleton`
- `Observable`

`Observable` is not the global event system. It is local object-level observation and should not replace Events/Halcyon model events.
