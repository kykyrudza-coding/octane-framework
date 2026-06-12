# Octane Framework

A modern PHP framework built around simplicity, performance, and developer experience.

## Features

- Modern PHP 8.4+
- Strong typing
- Modular architecture
- Service container
- Event system
- Query Builder
- Halcyon ORM
- Validation
- HTTP Routing
- CLI tooling

## Installation

```bash
composer require octane/framework
```

## Example

```php
use Horizon\Arch\Application;

$app = Application::configure(
    basePath: dirname(__DIR__)
)->create();
```

## Requirements

- PHP 8.4+

## License

MIT
