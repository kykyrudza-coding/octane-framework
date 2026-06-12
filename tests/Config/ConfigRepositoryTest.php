<?php

declare(strict_types=1);

namespace Tests\Config;

use Horizon\Arch\Config\ConfigRepository;
use PHPUnit\Framework\TestCase;

class ConfigRepositoryTest extends TestCase
{
    private ConfigRepository $config;

    protected function setUp(): void
    {
        $this->config = new ConfigRepository;
    }

    public function test_get_returns_value_by_simple_key(): void
    {
        $this->config->set('app', ['name' => 'Octane']);

        $this->assertSame(['name' => 'Octane'], $this->config->get('app'));
    }

    public function test_get_resolves_dot_notation(): void
    {
        $this->config->set('app', ['timezone' => 'UTC', 'debug' => true]);

        $this->assertSame('UTC', $this->config->get('app.timezone'));
    }

    public function test_get_resolves_deep_dot_notation(): void
    {
        $this->config->set('database', [
            'connections' => [
                'mysql' => ['host' => '127.0.0.1'],
            ],
        ]);

        $this->assertSame('127.0.0.1', $this->config->get('database.connections.mysql.host'));
    }

    public function test_get_returns_default_when_key_missing(): void
    {
        $result = $this->config->get('missing.key', 'fallback');

        $this->assertSame('fallback', $result);
    }

    public function test_get_returns_null_default_when_not_specified(): void
    {
        $this->assertNull($this->config->get('nonexistent'));
    }

    public function test_set_stores_top_level_value(): void
    {
        $this->config->set('app', ['name' => 'Octane']);

        $this->assertSame(['name' => 'Octane'], $this->config->get('app'));
    }

    public function test_has_returns_true_for_existing_top_level_key(): void
    {
        $this->config->set('app', []);

        $this->assertTrue($this->config->has('app'));
    }

    public function test_has_returns_false_for_missing_key(): void
    {
        $this->assertFalse($this->config->has('nonexistent'));
    }

    public function test_all_returns_all_items(): void
    {
        $this->config->set('app', ['name' => 'Octane']);
        $this->config->set('database', ['driver' => 'mysql']);

        $all = $this->config->all();

        $this->assertArrayHasKey('app', $all);
        $this->assertArrayHasKey('database', $all);
        $this->assertCount(2, $all);
    }
}
