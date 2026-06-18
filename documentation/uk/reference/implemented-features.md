# Реалізовані можливості

Підтверджено кодом і тестами:

- application singleton і bootstrap builder;
- dependency injection container;
- transient, singleton, instance, alias і path bindings;
- constructor autowiring і circular dependency detection;
- service providers і priority;
- `.env` та environment-specific файли;
- configuration repository з dot notation read;
- config-driven application providers;
- config-driven route files і route groups;
- GET/POST/PUT/PATCH/DELETE routing;
- route parameters, nested prefixes, middleware і name prefixes;
- fallback routes;
- HTTP request context;
- direct `$request->validate(...)`;
- controller/closure argument injection;
- FormRequest injection and validation;
- immutable base response modifiers;
- JSON, redirect, no-content і view responses;
- global/group/route middleware pipeline;
- console, JSON і HTML exception renderers;
- Prism escaped/raw output, directives, layouts, imports і components;
- Prism config для view path, extensions, cache, components і directives;
- bcrypt і Argon2id hashing з config options;
- database connection manager, drivers, schema builder, migrations і seed commands;
- QueryBuilder;
- Halcyon ORM metadata, hydration, relations і QueryBuilder mapper;
- DTO mapping, collections, casts, metadata і serialization;
- validation rules, error bags, validated data і presence verifier contract;
- generated API docs route і command;
- Vite manifest/dev-server helper.

Статус означає наявність робочого code path, але не гарантує production completeness. Відомі edge cases наведені в [обмеженнях](known-limitations.md).
