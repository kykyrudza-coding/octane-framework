# Відсутні та незавершені можливості

Цей список описує поточний стан framework після додавання ORM, DTO і validation.

## Відсутні підсистеми

- events;
- cache store abstraction;
- logging channel abstraction;
- authentication/authorization;
- sessions;
- queues;
- filesystem;
- mail/notifications;
- OpenAPI request/response validation.

## Незавершені або обмежені частини

- route URL generator за name;
- route constraints, optional parameters, casting і model binding;
- automatic JSON body parsing for `Request`;
- database-backed validation presence verifier;
- config cache/publish commands;
- session-backed redirects/flash data;
- production-ready logging/reporting pipeline;
- cache invalidation commands для Prism/DTO/Halcyon metadata;
- full resource routing;
- pagination integration in QueryBuilder.

## Частково готові заготовки

- деякі support helpers/value objects мають обмежений API;
- pagination classes ще не інтегровані в QueryBuilder;
- static helper/facade APIs не всюди використовують container binding.

README верхнього рівня може згадувати roadmap можливості. Ця сторінка описує фактичний стан поточного source code.
