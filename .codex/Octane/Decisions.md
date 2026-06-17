# Decisions

## Accepted

- Octane is the public framework name.
- Horizon is the PHP namespace.
- Current repo shape is one monolithic package.
- Future split into `octane/*` packages is possible but not active implementation.
- `components.json` + service providers are the current component discovery mechanism.
- `../octane-application` is a playground and DX draft.
- QueryBuilder is the next major module.
- DB facade stays low-level.
- QueryBuilder facade is named `QueryBuilder`.
- QueryBuilder supports both `table()` and `for()`.
- QueryBuilder `get()` should return an ItemsList-like collection.
- QueryBuilder internals should move toward separate builders.
- Grammar layer should support MySQL/PostgreSQL/SQLite through contracts.
- Halcyon is Data Mapper, not Active Record.
- Models use normal PHP typed properties.
- Relations are methods returning `Relation::hasMany(...)` etc.
- `hidden()` can stay in model for safety.
- `visible()` belongs in ApiResource.
- `guarded()` is not accepted right now.
- Password hashing belongs to casts/mutators, not `guarded()`.
- Observers and scopes should support both model-level and provider/facade registration.
- Global scopes for all models are needed later and must be disable-able per query.
- `UserDefinition` / `ModelDefinition` is rejected for now.
- Halcyon facade configures ORM; it does not query.
- Model metadata cache is `halcyon.metadata.cache`.
- DTO package is deferred.
- DTO attributes only include mapping helpers such as `MapFrom` and `MapTo`.
- Validation target is fluent rules + FormRequest + direct request validation.
- ApiResource should be stateless.
- Console command names should be static to avoid early command instantiation.
- Console rich output utilities stay in Console.
- Events are deferred until concrete emitters exist.
- Config is loaded before providers by `LoadConfiguration`.
- Current routing is not MVP; it already has groups/names/fallback/middleware.
- Route facade is the primary routing API.
- `RouteDTO` remains behaviorless.
- URL generator is deferred.
- Array-to-JSON response normalization is deferred.

## Rejected

- Active Record style model querying.
- `User::where()`, `User::query()`, `$user->save()`, `$user->delete()`.
- Relation attributes/properties as primary relation syntax.
- `UserDefinition` / `ModelDefinition` as current ORM configuration layer.
- DTO validation attributes as main validation system.
- Building lifecycle on Events before QueryBuilder/Halcyon.
- Treating `../octane-application` future-API drafts as production code.

## Deferred

- Multi-package repo split.
- Public route URL generator.
- Replacing `PendingRoute::__destruct()` auto-registration.
- Array response normalization to JSON.
- Full Events package.
- DTO package.
- Final password hashing/casts/mutators design.
- Public README/documentation sync.
