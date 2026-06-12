<?php

declare(strict_types=1);

namespace Tests\Prism\Engine;

use Horizon\Contracts\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Component\ComponentResolverContract;
use Horizon\Prism\Prism\Engine\PrismEngine;
use PHPUnit\Framework\TestCase;

class PrismEngineTest extends TestCase
{
    private string $cachePath;
    private string $viewsPath;
    private PrismCompilerContract $compiler;
    private ComponentResolverContract $componentResolver;

    protected function setUp(): void
    {
        $this->cachePath = sys_get_temp_dir() . '/prism_cache_' . uniqid();
        $this->viewsPath = sys_get_temp_dir() . '/prism_views_' . uniqid();

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
        if (!is_dir($this->viewsPath)) {
            mkdir($this->viewsPath, 0777, true);
        }

        $this->compiler = $this->createMock(PrismCompilerContract::class);
        $this->componentResolver = $this->createMock(ComponentResolverContract::class);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->cachePath);
        $this->removeDirectory($this->viewsPath);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function test_engine_evaluates_template(): void
    {
        $engine = new PrismEngine(
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath
        );

        $viewFile = $this->viewsPath . '/test.php';
        file_put_contents($viewFile, 'Hello <?php echo $name; ?>');

        $output = $engine->render($viewFile, ['name' => 'Prism']);

        $this->assertSame('Hello Prism', $output);
    }
}
