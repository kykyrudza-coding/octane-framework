# Application Skeleton

## Current Role

`../octane-application` is both:

- playground for checking framework behavior
- DX/API draft for future framework ergonomics

When application code references APIs that do not exist yet, treat it as a sketch unless explicitly asked to make that code runnable.

## Current Directory Choice

Do not rename the current skeleton directories now. The user said the current skeleton directories are acceptable.

Historical ideas included `var/` instead of Laravel-like `storage/`, but do not perform structural churn unless asked.

## Application Code Drafts

The current application may contain drafts for:

- FormRequest
- DTO
- ApiResource
- ORM model metadata
- observers/scopes
- access tokens/auth
- future QueryBuilder/Halcyon APIs

These drafts communicate desired DX.

## Bootstrap

`public/index.php` should use `RequestContext::capture()`.

`boot/app.php` configures:

- paths/environment
- providers
- routing
- middleware
- exceptions

Avoid leaking unnecessary global constants into boot files when application path methods can provide the same information, but do not refactor existing skeleton paths without a concrete task.

## Make Commands

Future make commands should support configurable output paths.

The skeleton may keep a default app structure while config allows users to customize where controllers, models, actions, etc. are generated.
