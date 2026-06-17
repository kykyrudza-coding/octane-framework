# Routing And HTTP

## Current Routing State

Routing is already beyond MVP.

Implemented or intended as current API:

- `Route` facade as primary route registration API
- `get`, `post`, `put`, `patch`, `delete`
- URI parameters like `/users/{id}`
- pending route DSL
- route names
- prefix groups
- middleware groups
- fallback route
- web/api route group tagging
- `RouteCollection::getByName()`

`PendingRoute::__destruct()` auto-registering routes is accepted for now as part of the current DSL, but it is not final. It should be documented as deferred and may be replaced with a more explicit mechanism later.

## Route DTO

`RouteDTO` should remain a readonly data object without behavior.

Do not move route behavior into `RouteDTO`.

## URL Generation

Named routes exist, but a public URL generator is deferred.

Possible future API:

```php
route('users.show', ['id' => 1]);
```

Do not implement this before routing/cache decisions stabilize.

## Request Context

`RequestContext` is not a `Request` proxy.

It is lifecycle state:

- current request
- matched route
- route params
- response

Accepted API direction:

- `RequestContext::capture()` creates `new Request()`
- `getRequest()`
- `getRoute()`
- `getParams()`
- `getParam()`
- `setRoute()`
- `setParams()`
- `setResponse()`
- `hasResponse()`

Do not add `method()`, `uri()`, or `input()` proxy methods to `RequestContext`. Use `getRequest()`.

## Controller Invocation

`InvokeController` should:

- support closures
- support array actions `[Controller::class, 'method']`
- support string actions `Controller@method`
- resolve controller dependencies through the container
- inject `RequestContract` or request subclass
- inject other non-builtin dependencies through the container
- match named route params by parameter name
- fallback to positional params
- cast scalar route params by PHP type-hint

Route parameter casting is accepted:

```php
Route::get('/users/{id}', [UserController::class, 'show']);

public function show(int $id): ResponseContract
```

`$id` should be an integer.

## Response Normalization

Current/fixed behavior:

- `ResponseContract` returns as-is
- scalar/stringable/null results become regular response body

Array-to-JSON response normalization is not current behavior. Put it in roadmap, not immediate implementation.

## Middleware

Current design supports:

- global middleware
- route group middleware
- route middleware

Middleware order should stay predictable and be documented when stabilized.
