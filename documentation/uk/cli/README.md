# CLI

Skeleton має executable entrypoint `octane`:

```php
#!/usr/bin/env php
<?php

define('APP_ROOT', __DIR__);
define('OCTANE_START', microtime(true));

require_once APP_ROOT.'/vendor/autoload.php';

$app = require_once APP_ROOT.'/boot/app.php';

exit($app->runCli($argv));
```

Console kernel і command registry реєструються framework providers. Built-in commands:

```bash
php octane list
php octane about
php octane start
php octane docs:api
php octane migrate
php octane migrate:rollback
php octane migrate:fresh
php octane migrate:reset
php octane make:migration
php octane db:seed
```

`start` читає defaults з `config/http.php`:

```php
'server' => [
    'host' => env('SERVER_HOST', '127.0.0.1'),
    'port' => (int) env('SERVER_PORT', 8000),
],
```

Command line arguments мають пріоритет:

```bash
php octane start 127.0.0.1 8080
```

## Application commands

Application command classes додаються через `config/console.php`:

```php
return [
    'commands' => [
        App\Console\Commands\ImportUsersCommand::class,
    ],
];
```

Command class має реалізовувати `CommandContract`, зазвичай через базовий `Horizon\Console\Command`:

```php
final class ImportUsersCommand extends Command
{
    public static function commandName(): string
    {
        return 'users:import';
    }

    public function description(): string
    {
        return 'Import users.';
    }

    public function handle($input, $output): int
    {
        $output->line('Imported.');

        return self::SUCCESS;
    }
}
```

Некоректні command classes з config не реєструються.
