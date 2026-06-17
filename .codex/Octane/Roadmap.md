# Roadmap

## Immediate

1. Build QueryBuilder infrastructure.
2. Add QueryBuilder contracts/provider/factory/facade.
3. Add builder classes and grammar contracts.
4. Add first useful table/class-convention query APIs.

## QueryBuilder Milestones

Milestone 1:

- contracts
- service provider
- factory
- facade
- grammar selection
- builder skeletons
- tests

Milestone 2:

- `table()`
- `for()`
- `select`
- `where`
- `order`
- `limit`
- `get`
- `first`
- `count`
- `exists`

Milestone 3:

- insert/create
- update
- delete
- joins
- raw expressions
- pagination
- ItemsList integration

## After QueryBuilder

1. Halcyon metadata parser and hydrator.
2. Model metadata cache.
3. Halcyon scopes and observers.
4. Validation/FormRequest.
5. DTO package.
6. ApiResource.
7. Events package when concrete emitters exist.
8. Auth/cache/session expansion.

## Maintenance Debt

PHPStan is not blocking current QueryBuilder development, but known debt should be tracked:

- contracts not matching implementations
- incomplete `ItemsList`
- DB facade `resolve()` issue
- schema compiler contract
- connection factory/manager contracts
- stale docblocks and unmatched PHPStan ignores

## Documentation

After these `.codex/Octane/*.md` files are created, keep them updated as decisions change.

Do not update public README/documentation automatically unless the user asks.
