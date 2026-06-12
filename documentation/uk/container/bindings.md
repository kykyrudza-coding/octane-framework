# Прив'язки в container

Container доступний через `container()`, `app()->getContainer()` або методи
application.

## Transient binding

```php
app()->bind(Mailer::class, SmtpMailer::class);
app()->bind('clock', fn ($container) => new SystemClock());
```

Кожен `make()` створює новий instance:

```php
$mailer = app(Mailer::class);
```

## Singleton

```php
app()->singleton(CacheStore::class, FileCacheStore::class);
```

Перший resolved object кешується для наступних викликів.

## Готовий instance

```php
app()->instance(LoggerInterface::class, $logger);
```

## Alias і path

```php
app()->bindAlias('logger', LoggerInterface::class);
app()->bindPath('path.uploads', app()->publicPath('uploads'));
```

`has()` повертає `true` лише для explicit binding, instance, path або alias.
Автоматично instantiable class не вважається зареєстрованим, хоча
`make(ClassName::class)` може його створити.
