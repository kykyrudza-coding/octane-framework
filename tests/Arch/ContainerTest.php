<?php

declare(strict_types=1);

namespace Tests\Arch;

use Horizon\Arch\Container;
use Horizon\Contracts\Arch\ContainerContract;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use stdClass;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container;
    }

    /**
     * Тест 1: bind + make — базова прив'язка через callable
     * Перевіряє, що контейнер може зареєструвати factory і створити інстанс.
     *
     * @throws ReflectionException
     */
    public function test_bind_resolves_via_callable(): void
    {
        $this->container->bind('foo', fn () => new stdClass);

        $result = $this->container->make('foo');

        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * Тест 2: bind повертає новий інстанс при кожному make
     * Не singleton — кожен виклик make() має повертати різні об'єкти.
     *
     * @throws ReflectionException
     */
    public function test_bind_returns_new_instance_each_time(): void
    {
        $this->container->bind('foo', fn () => new stdClass);

        $a = $this->container->make('foo');
        $b = $this->container->make('foo');

        $this->assertNotSame($a, $b);
    }

    /**
     * Тест 3: singleton завжди повертає один і той самий інстанс
     * Після першого resolve — кешується і перевикористовується.
     *
     * @throws ReflectionException
     */
    public function test_singleton_returns_same_instance(): void
    {
        $this->container->singleton('foo', fn () => new stdClass);

        $a = $this->container->make('foo');
        $b = $this->container->make('foo');

        $this->assertSame($a, $b);
    }

    /**
     * Тест 4: instance зберігає конкретний об'єкт
     * Контейнер повертає саме той об'єкт, який передали в instance().
     *
     * @throws ReflectionException
     */
    public function test_instance_stores_and_returns_exact_object(): void
    {
        $obj = new stdClass;
        $obj->name = 'test';

        $this->container->instance('foo', $obj);

        $result = $this->container->make('foo');

        $this->assertSame($obj, $result);
        $this->assertSame('test', $result->name);
    }

    /**
     * Тест 5: has перевіряє наявність bindings та instances
     * Повертає true для зареєстрованих, false для незареєстрованих.
     */
    public function test_has_checks_bindings_and_instances(): void
    {
        $this->assertFalse($this->container->has('foo'));
        $this->assertFalse($this->container->has('bar'));

        $this->container->bind('foo', fn () => new stdClass);
        $this->container->instance('bar', new stdClass);

        $this->assertTrue($this->container->has('foo'));
        $this->assertTrue($this->container->has('bar'));
        $this->assertFalse($this->container->has('baz'));
    }

    /**
     * Тест 6: autowiring — контейнер автоматично резолвить залежності конструктора
     * Якщо клас має type-hinted залежності, контейнер створює їх рекурсивно.
     *
     * @throws ReflectionException
     */
    public function test_autowiring_resolves_constructor_dependencies(): void
    {
        // NoDependency не вимагає нічого в конструкторі
        $result = $this->container->make(NoDependency::class);

        $this->assertInstanceOf(NoDependency::class, $result);

        // WithDependency вимагає NoDependency — контейнер повинен автоматично створити
        $result = $this->container->make(WithDependency::class);

        $this->assertInstanceOf(WithDependency::class, $result);
        $this->assertInstanceOf(NoDependency::class, $result->dep);
    }

    /**
     * Тест 7: контейнер резолвить інтерфейс через binding
     * Bind interface → concrete, потім make(interface) → отримуємо concrete.
     *
     * @throws ReflectionException
     */
    public function test_resolves_interface_via_binding(): void
    {
        $this->container->bind(ContainerContract::class, Container::class);

        $result = $this->container->make(ContainerContract::class);

        $this->assertInstanceOf(Container::class, $result);
    }

    /**
     * Тест 8: circular dependency detection
     * Якщо A залежить від B, а B від A — має кинути RuntimeException.
     *
     * @throws ReflectionException
     */
    public function test_detects_circular_dependency(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Cc]ircular/');

        $this->container->make(CircularA::class);
    }

    /**
     * Тест 9: параметр з default value використовується як fallback
     * Якщо немає binding для типу — використовується default value конструктора.
     *
     * @throws ReflectionException
     */
    public function test_uses_default_value_when_no_binding(): void
    {
        $result = $this->container->make(WithDefault::class);

        $this->assertInstanceOf(WithDefault::class, $result);
        $this->assertSame('default', $result->value);
    }

    /**
     * Тест 10: binding має пріоритет над default value
     * Якщо є binding для типу — він резолвиться, навіть якщо є default = null.
     *
     * @throws ReflectionException
     */
    public function test_binding_takes_priority_over_default_value(): void
    {
        $this->container->bind(NoDependency::class, fn () => new NoDependency);

        $result = $this->container->make(WithNullableDefault::class);

        $this->assertInstanceOf(NoDependency::class, $result->dep);
    }
}

// --- Допоміжні класи для тестів ---

class NoDependency {}

readonly class WithDependency
{
    public function __construct(
        public NoDependency $dep,
    ) {}
}

class CircularA
{
    public function __construct(public CircularB $b) {}
}

class CircularB
{
    public function __construct(public CircularA $a) {}
}

readonly class WithDefault
{
    public function __construct(
        public string $value = 'default',
    ) {}
}

readonly class WithNullableDefault
{
    public function __construct(
        public ?NoDependency $dep = null,
    ) {}
}
