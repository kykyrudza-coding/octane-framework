<?php

declare(strict_types=1);

namespace Tests\Docs;

use Horizon\Arch\Application;
use Horizon\Docs\ApiDocGenerator;
use Horizon\Docs\Controllers\ApiDocsController;
use Horizon\Routing\RouteCollection;
use Horizon\Routing\RouteDTO;
use PHPUnit\Framework\TestCase;

final class DocsTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'octane-docs-test-'.bin2hex(random_bytes(6));
        mkdir($this->tmp, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->tmp);

        parent::tearDown();
    }

    public function test_generator_creates_octane_styled_index_and_class_pages(): void
    {
        $output = $this->tmp.DIRECTORY_SEPARATOR.'api-docs';

        $classes = (new ApiDocGenerator)->generate(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Horizon'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'ValueObjects',
            $output
        );

        $this->assertContains(\Horizon\Support\ValueObjects\Money::class, $classes);
        $this->assertFileExists($output.DIRECTORY_SEPARATOR.'index.html');
        $this->assertFileExists($output.DIRECTORY_SEPARATOR.'providers.html');
        $this->assertFileExists($output.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'support.html');
        $this->assertFileExists($output.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Horizon'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'ValueObjects'.DIRECTORY_SEPARATOR.'Money.html');

        $index = (string) file_get_contents($output.DIRECTORY_SEPARATOR.'index.html');
        $modulePage = (string) file_get_contents($output.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'support.html');
        $classPage = (string) file_get_contents($output.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Horizon'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'ValueObjects'.DIRECTORY_SEPARATOR.'Money.html');

        $this->assertStringContainsString('Octane API Reference', $index);
        $this->assertStringContainsString('--bg: #f8f9fb', $index);
        $this->assertStringContainsString('Packages', $index);
        $this->assertStringContainsString('Service providers', (string) file_get_contents($output.DIRECTORY_SEPARATOR.'providers.html'));
        $this->assertStringContainsString('Horizon\Support\ValueObjects', $modulePage);
        $this->assertStringContainsString('Horizon\Support\ValueObjects\Money', $classPage);
        $this->assertStringContainsString('Properties', $classPage);
        $this->assertStringContainsString('$amount', $classPage);
        $this->assertStringContainsString('function fromString', $classPage);
    }

    public function test_docs_controller_serves_generated_index(): void
    {
        new Application($this->tmp);

        $docsPath = $this->tmp.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'api-docs';
        mkdir($docsPath, 0775, true);
        file_put_contents($docsPath.DIRECTORY_SEPARATOR.'index.html', '<html>API</html>');

        $response = (new ApiDocsController)->index();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('<html>API</html>', $response->getBody());
        $this->assertSame('text/html; charset=UTF-8', $response->getHeader('Content-Type'));
    }

    public function test_docs_controller_serves_nested_asset_with_content_type(): void
    {
        new Application($this->tmp);

        $docsPath = $this->tmp.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'api-docs'.DIRECTORY_SEPARATOR.'assets';
        mkdir($docsPath, 0775, true);
        file_put_contents($docsPath.DIRECTORY_SEPARATOR.'app.css', 'body{}');

        $response = (new ApiDocsController)->show('assets/app.css');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('body{}', $response->getBody());
        $this->assertSame('text/css; charset=UTF-8', $response->getHeader('Content-Type'));
    }

    public function test_docs_controller_rejects_path_traversal(): void
    {
        new Application($this->tmp);

        $docsPath = $this->tmp.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'api-docs';
        mkdir($docsPath, 0775, true);

        $response = (new ApiDocsController)->show('../secret.txt');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_wildcard_routes_capture_nested_paths(): void
    {
        $routes = new RouteCollection;
        $routes->add(new RouteDTO(
            methods: ['GET'],
            uri: '/_octane/api/{path*}',
            action: fn () => null
        ));

        $match = $routes->match('GET', '/_octane/api/classes/Horizon/Support/Money.html');

        $this->assertNotNull($match);
        $this->assertSame('classes/Horizon/Support/Money.html', $match->getParams()['path']);
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $child = $path.DIRECTORY_SEPARATOR.$item;
            if (is_dir($child)) {
                $this->deleteDirectory($child);
            } else {
                unlink($child);
            }
        }

        rmdir($path);
    }
}
