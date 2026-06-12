<?php

declare(strict_types=1);

namespace Tests\Env;

use Horizon\Arch\Application;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApplicationEnvironmentTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new ReflectionClass(Application::class);
        $prop = $ref->getProperty('instance');
        $prop->setValue(null, null);
    }

    public function test_default_environment_is_production(): void
    {
        $app = new Application;

        $this->assertSame('production', $app->getEnvironment());
    }

    public function test_set_environment_changes_environment(): void
    {
        $app = new Application;
        $app->setEnvironment('development');

        $this->assertSame('development', $app->getEnvironment());
    }

    public function test_is_production_returns_true_for_production(): void
    {
        $app = new Application;
        $app->setEnvironment();

        $this->assertTrue($app->isProduction());
    }

    public function test_is_development_returns_true_for_development(): void
    {
        $app = new Application;
        $app->setEnvironment('development');

        $this->assertTrue($app->isDevelop());
    }

    public function test_is_testing_returns_true_for_testing(): void
    {
        $app = new Application;
        $app->setEnvironment('testing');

        $this->assertTrue($app->isTesting());
    }

    public function test_is_local_returns_true_for_local(): void
    {
        $app = new Application;
        $app->setEnvironment('local');

        $this->assertTrue($app->isLocal());
    }

    public function test_default_env_file_name(): void
    {
        $app = new Application;

        $this->assertSame('.env', $app->getEnvironmentFile());
    }

    public function test_environment_file_setter_normalizes_separators(): void
    {
        $app = new Application;
        $app->environmentFile('/base/path/.env');

        $expected = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/base/path/.env');

        $this->assertSame($expected, $app->getEnvironmentFile());
    }

    public function test_custom_env_files_are_stored_correctly(): void
    {
        $app = new Application;
        $app->developmentEnvironmentFile('/app/.env.development');
        $app->productionEnvironmentFile('/app/.env.production');
        $app->testingEnvironmentFile('/app/.env.testing');
        $app->localEnvironmentFile('/app/.env.local');

        $this->assertStringEndsWith('.env.development', $app->getDevelopmentEnvironmentFile());
        $this->assertStringEndsWith('.env.production', $app->getProductionEnvironmentFile());
        $this->assertStringEndsWith('.env.testing', $app->getTestingEnvironmentFile());
        $this->assertStringEndsWith('.env.local', $app->getLocalEnvironmentFile());
    }
}
