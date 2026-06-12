# Реалізовані можливості

Підтверджені кодом і тестами:

- application singleton і bootstrap builder;
- dependency injection container;
- transient, singleton, instance, alias і path bindings;
- constructor autowiring та circular dependency detection;
- service providers і priority;
- `.env` + environment-specific files;
- configuration repository з dot notation read;
- GET/POST/PUT/PATCH/DELETE routing;
- route parameters, nested prefixes, middleware та name prefixes;
- fallback routes;
- HTTP request context;
- controller/closure argument injection;
- immutable base response modifiers;
- JSON, redirect, no-content і view responses;
- global/group/route middleware pipeline;
- console, JSON та HTML exception renderers;
- Prism escaped/raw output, directives, layouts, imports і components;
- bcrypt та Argon2id hashing;
- Vite manifest/dev-server helper.

Статус означає наявність робочого code path, але не гарантує production
completeness. Відомі edge cases наведені в
[обмеженнях](known-limitations.md).
