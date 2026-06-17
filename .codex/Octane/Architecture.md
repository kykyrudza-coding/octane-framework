# Architecture

## Arch

`Horizon\Arch` is the framework glue layer. It is not a separate package right now.

Arch owns:

- `Application`
- `ApplicationBuilder`
- bootstrap pipes
- provider registration and booting
- application lifecycle
- HTTP/console kernel orchestration

Arch must not become a general-purpose dumping ground. It should not own routing, request/response abstractions, database, ORM, validation, auth, cache, or events.

## Application

`Application` is intentionally smaller than Laravel's Foundation application.

Responsibilities:

- container access
- provider registration
- lifecycle entrypoints
- request context handoff
- paths/environment helpers
- termination callbacks

Current public shape:

```php
Application::configure(basePath: dirname(__DIR__))
    ->withEnvironment(...)
    ->withPaths(...)
    ->withProviders(...)
    ->withRouting(...)
    ->withMiddleware(...)
    ->withExceptions(...)
    ->create();
```

The app exposes `bind`, `singleton`, `instance`, `make`, `has`, `bindPath`, and `bindAlias` as its container-facing API.

`booted` and `runningInConsole` are real state and should stay meaningful.

## Paths

Keep the current path implementation unless the user asks to change it.

The accepted approach is a mix of Application path methods and container path bindings. The current implementation is acceptable:

- fallback path methods like `appPath()`, `configPath()`, `dbPath()`, `varPath()`, `cachePath()`, `logPath()`, `uiPath()`
- container bindings like `path.base`, `path.app`, `path.config`, etc.
- global path helpers are desired

Do not rename skeleton directories now. The current application directory choices are accepted by the user.

Global helpers should include at least:

- `base_path()`
- `app_path()`
- `config_path()`
- `db_path()`
- `var_path()`
- `cache_path()`
- `log_path()`
- `ui_path()`

## Bootstrap Order

Configuration must load before service providers. Therefore `LoadConfiguration` should create/register the config repository directly, not rely on a `ConfigServiceProvider`.

Accepted order concept:

1. bind paths / core bindings
2. exception handling
3. load environment
4. load configuration
5. bind aliases
6. register providers
7. boot providers
8. apply builder callbacks

`withPaths()` and `withEnvironment()` currently execute their callbacks immediately before `create()`. This is accepted because paths/env must be known before bootstrap pipes run.

## Pipeline

The active implementation puts `Pipeline` under `Horizon\Support\Pipeline`.

This is accepted as current code truth. Earlier discussions considered keeping pipeline inside Arch as a lifecycle-only tool, but current code uses Support.

## Config

No `ConfigServiceProvider` is needed for config bootstrap.

`LoadConfiguration` should:

- resolve config path
- load PHP config files
- create `ConfigRepository`
- register `ConfigRepositoryContract` in the container before providers run

## Lifecycle Events

Do not build the application lifecycle on EventBus yet.

For now:

- providers
- `terminating()`
- `booted` state

are enough.

`booting/booted` callbacks can be considered later, but are not a current priority.
