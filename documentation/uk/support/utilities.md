# Допоміжні класи

## `Fluent`

Mutable key-value object із property access:

```php
$options = new Fluent(['timeout' => 10]);
$options->retries = 3;

$options->get('timeout');
$options->set('debug', true);
$options->toArray();
$options->toJson();
```

## `Macroable`

Trait додає static registry dynamic methods:

```php
RouteLikeClass::macro('health', fn () => 'ok');
RouteLikeClass::health();
```

## `Conditionable`

```php
$builder
    ->when($condition, fn ($value) => $value->enable())
    ->unless($other, fn ($value) => $value->disable());
```

## `Timer`

```php
$timer = Timer::start();
doWork();
$milliseconds = $timer->stop();
```

`Benchmark::measure()` повертає measurements, але
`BenchmarkResult::average()` у поточному коді має дефект із
`array_sum($iterations)`. Не використовуйте average до виправлення.

`HttpStatus` містить поширені status codes, `label()` та methods
`isSuccess()`, `isRedirect()`, `isClientError()`, `isServerError()`.

Інші класи Support можуть бути порожніми або незавершеними. Їх перелічено в
[довідці](../reference/unavailable-features.md).
