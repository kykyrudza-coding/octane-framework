<?php

declare(strict_types=1);

namespace Tests\Arch;

use Closure;
use Horizon\Arch\Container;
use Horizon\Arch\Pipeline\PipeInterface;
use Horizon\Arch\Pipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use stdClass;

class PipelineTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container;
    }

    /**
     * Тест 1: Pipeline без pipes — destination отримує оригінальний payload
     * Якщо pipes пустий, payload просто проходить до destination.
     */
    public function test_empty_pipeline_passes_payload_to_destination(): void
    {
        $result = new Pipeline($this->container)
            ->send('hello')
            ->through([])
            ->then(fn (string $payload) => strtoupper($payload));

        $this->assertSame('HELLO', $result);
    }

    /**
     * Тест 2: Один pipe трансформує payload
     * Pipe модифікує дані перед передачею далі.
     */
    public function test_single_pipe_transforms_payload(): void
    {
        $result = new Pipeline($this->container)
            ->send('hello')
            ->through([AddExclamationPipe::class])
            ->then(fn (string $payload) => $payload);

        $this->assertSame('hello!', $result);
    }

    /**
     * Тест 3: Кілька pipes виконуються в правильному порядку
     * Pipes виконуються зліва направо (перший → другий → destination).
     */
    public function test_multiple_pipes_execute_in_order(): void
    {
        $result = new Pipeline($this->container)
            ->send('hello')
            ->through([
                AddExclamationPipe::class,
                UpperCasePipe::class,
            ])
            ->then(fn (string $payload) => $payload);

        // Спочатку AddExclamation: "hello" → "hello!"
        // Потім UpperCase: "hello!" → "HELLO!"
        $this->assertSame('HELLO!', $result);
    }

    /**
     * Тест 4: Pipe може зупинити pipeline (не викликати $next)
     * Якщо pipe не викликає $next, решта pipes і destination не виконуються.
     */
    public function test_pipe_can_stop_pipeline(): void
    {
        $result = new Pipeline($this->container)
            ->send('hello')
            ->through([
                StopPipe::class,
                AddExclamationPipe::class, // цей НЕ повинен виконатись
            ])
            ->then(fn (string $payload) => $payload.'_destination');

        $this->assertSame('STOPPED', $result);
    }

    /**
     * Тест 5: Pipeline передає payload як об'єкт (мутабельний)
     * Перевіряє, що об'єкти передаються по reference через pipeline.
     */
    public function test_pipeline_with_object_payload(): void
    {
        $payload = new stdClass;
        $payload->value = 0;

        $result = new Pipeline($this->container)
            ->send($payload)
            ->through([IncrementPipe::class, IncrementPipe::class])
            ->then(fn ($p) => $p);

        $this->assertSame(2, $result->value);
    }

    /**
     * Тест 6: Pipeline резолвить pipes через контейнер (DI injection)
     * Якщо pipe має залежності в конструкторі, контейнер їх інжектить.
     */
    public function test_pipes_are_resolved_through_container(): void
    {
        $this->container->instance('prefix', (object) ['value' => '[PREFIX] ']);

        $this->container->bind(PrefixPipe::class, fn ($c) => new PrefixPipe($c->make('prefix')));

        $result = new Pipeline($this->container)
            ->send('message')
            ->through([PrefixPipe::class])
            ->then(fn (string $payload) => $payload);

        $this->assertSame('[PREFIX] message', $result);
    }

    /**
     * Тест 7: Pipeline з callable destination (не тільки Closure)
     * Destination може бути callable string (class name).
     */
    public function test_pipeline_with_closure_destination(): void
    {
        $result = new Pipeline($this->container)
            ->send(42)
            ->through([])
            ->then(fn (int $x) => $x * 2);

        $this->assertSame(84, $result);
    }

    /**
     * Тест 8: Pipeline fluent API — send/through/then chaining
     * Перевіряє, що send() і through() повертають $this для chaining.
     */
    public function test_fluent_api_returns_pipeline_instance(): void
    {
        $pipeline = new Pipeline($this->container);

        $step1 = $pipeline->send('test');
        $step2 = $step1->through([]);

        $this->assertSame($pipeline, $step1);
        $this->assertSame($pipeline, $step2);
    }
}

// --- Допоміжні Pipe класи ---

class AddExclamationPipe implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): mixed
    {
        return $next($payload.'!');
    }
}

class UpperCasePipe implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): mixed
    {
        return $next(strtoupper($payload));
    }
}

class StopPipe implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): mixed
    {
        // Не викликаємо $next — pipeline зупиняється
        return 'STOPPED';
    }
}

class IncrementPipe implements PipeInterface
{
    public function handle(mixed $payload, Closure $next): mixed
    {
        $payload->value++;

        return $next($payload);
    }
}

readonly class PrefixPipe implements PipeInterface
{
    public function __construct(private object $prefix) {}

    public function handle(mixed $payload, Closure $next): mixed
    {
        return $next($this->prefix->value.$payload);
    }
}
