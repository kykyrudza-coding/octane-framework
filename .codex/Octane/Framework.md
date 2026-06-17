# Octane Framework Context

This directory is the canonical working memory for Octane Framework decisions, goals, and open questions.

## Identity

- Public framework name: Octane.
- Main PHP namespace: `Horizon`.
- Current repository: `octane-framework`.
- Application playground/skeleton repository: `../octane-application`.
- Current framework shape: one monolithic Composer package with `src/Horizon/*`.
- Future direction: may evolve into separate `octane/*` packages, but the active implementation is a monolith.
- Components are discovered through `components.json` files and service providers.

## Repository Role

`octane-framework` contains the framework source code.

`../octane-application` is currently a playground and DX/API draft. Some application code intentionally references future APIs that are not implemented yet. Treat it as a design sketch unless the user explicitly asks to make it runnable.

## Current Priorities

1. Build `QueryBuilder`.
2. Keep the existing skeleton/application structure as-is for now.
3. Do not prioritize PHPStan over feature progress while tests are passing.
4. Defer Events until after Halcyon/Auth or until concrete emitters exist.

## Documentation Map

- [Architecture.md](Architecture.md): application lifecycle, paths, providers, config, pipeline.
- [Routing-Http.md](Routing-Http.md): routing, request context, controller invocation, responses.
- [QueryBuilder.md](QueryBuilder.md): QueryBuilder API, structure, milestones.
- [Halcyon.md](Halcyon.md): ORM philosophy, models, relations, scopes, observers.
- [DTO-Validation-Resources.md](DTO-Validation-Resources.md): DTO, FormRequest, validation, resources.
- [Console-Database-Support.md](Console-Database-Support.md): console, database, support utilities.
- [Application-Skeleton.md](Application-Skeleton.md): application structure and DX draft status.
- [Roadmap.md](Roadmap.md): implementation order.
- [Decisions.md](Decisions.md): accepted/rejected/deferred architectural choices.
- [OpenQuestions.md](OpenQuestions.md): unresolved items and deferred decisions.
