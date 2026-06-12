<?php

declare(strict_types=1);

namespace Tests\Arch;

use Horizon\Arch\Application;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Container;
use Horizon\Support\Providers\ServiceProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

class ApplicationTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset static instance between tests
        $ref = new ReflectionClass(Application::class);
        $prop = $ref->getProperty('instance');
        $prop->setValue(null, null);

        // Reset static counter
        $ref2 = new ReflectionClass(CountingProvider::class);
        $prop2 = $ref2->getProperty('count');
        $prop2->setValue(null, 0);
    }

    public function test_configure_returns_application_builder(): void
    {
        $builder = Application::configure(basePath: __DIR__);

        $this->assertInstanceOf(ApplicationBuilder::class, $builder);
    }

    public function test_get_instance_throws_before_init(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application has not been initialized');

        Application::getInstance();
    }

    public function test_get_instance_works_after_construction(): void
    {
        $app = new Application;

        $this->assertSame($app, Application::getInstance());
    }

    public function test_container_is_available(): void
    {
        $app = new Application;

        $this->assertInstanceOf(Container::class, $app->getContainer());
    }

    public function test_bind_and_make_delegates_to_container(): void
    {
        $app = new Application;
        $app->bind('foo', fn () => new stdClass);

        $result = $app->make('foo');

        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function test_singleton_via_application(): void
    {
        $app = new Application;
        $app->singleton('bar', fn () => new stdClass);

        $a = $app->make('bar');
        $b = $app->make('bar');

        $this->assertSame($a, $b);
    }

    public function test_instance_via_application(): void
    {
        $app = new Application;
        $obj = new stdClass;
        $obj->name = 'test';

        $app->instance('baz', $obj);

        $this->assertSame($obj, $app->make('baz'));
    }

    public function test_has_via_application(): void
    {
        $app = new Application;

        $this->assertFalse($app->has('missing'));

        $app->bind('present', fn () => 'ok');
        $this->assertTrue($app->has('present'));
    }

    public function test_register_provider_calls_register(): void
    {
        $app = new Application;
        $provider = new TestServiceProvider($app);

        $app->registerProvider($provider);

        $this->assertTrue($app->has('test.registered'));
    }

    public function test_register_provider_prevents_duplicates(): void
    {
        $app = new Application;
        $provider = new CountingProvider($app);

        $app->registerProvider($provider);
        $app->registerProvider($provider);

        // Should have been registered only once
        $this->assertSame(1, $app->make('counter'));
    }

    public function test_boot_providers(): void
    {
        $app = new Application;
        $provider = new BootableProvider($app);

        $app->registerProvider($provider);
        $app->bootProviders();

        $this->assertTrue($app->has('booted'));
    }

    public function test_version_returns_string(): void
    {
        $version = Application::version();

        $this->assertIsString($version);
        $this->assertStringContainsString('Octane', $version);
    }
}

// --- Test providers ---

class TestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('test.registered', fn () => true);
    }
}

class CountingProvider extends ServiceProvider
{
    private static int $count = 0;

    public function register(): void
    {
        self::$count++;
        $count = self::$count;
        $this->app->bind('counter', fn () => $count);
    }
}

class BootableProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind('booted', fn () => true);
    }
}
