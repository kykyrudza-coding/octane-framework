# Open Questions

These are unresolved or intentionally deferred decisions extracted from old discussions and the 55-question clarification pass.

## QueryBuilder

- Exact class names and namespace layout for `SelectBuilder`, `InsertBuilder`, `UpdateBuilder`, and `DeleteBuilder`.
- Exact `ItemsList` API needed by QueryBuilder results.
- How far `QueryBuilder::for(Model::class)` should go before Halcyon metadata exists.
- How raw expressions should be represented safely.
- Whether pagination belongs in QueryBuilder or Support/Pagination.

## Halcyon

- Final custom cast registration API.
- Whether casts are configured through model method, registry, attributes, or mixed approach.
- Exact mutator API for password hashing and other write transformations.
- How global scopes are disabled per query.
- Event names and lifecycle order for observers.
- Exact metadata cache storage backend and invalidation rules.

## Routing And HTTP

- Whether to keep or replace `PendingRoute::__destruct()` auto-registration.
- Public URL generator shape: `route()`, `url()`, separate `UrlGenerator`, or facade.
- Future response normalizer rules, especially arrays to JSON.
- Exact middleware ordering contract documentation.

## Application And Architecture

- Future repo split strategy from monolith to `octane/*`.
- Whether Application needs `booting/booted` callbacks before Events package.
- Whether path values should remain duplicated in Application properties and container path bindings long term.

## DTO, Validation, Resources

- Exact `MapFrom` / `MapTo` attribute syntax.
- Exact validated-data object returned by `$request->validate(...)`.
- Final method names: `dto()`, `toDto()`, `validated()->toDto()`.
- ApiResource collection/pagination response shape.

## Console And Database

- Final command signature/input parser shape beyond `argv` argument access.
- Whether console UI should detect non-TTY and disable animations/colors.
- Exact database metadata cache purpose versus Halcyon model metadata cache.

## Documentation

- Whether public README should later include this context or stay concise.
- Whether Ukrainian documentation should mirror these internal decisions.
