<?php

declare(strict_types=1);

namespace Tests\Prism\Engine;

use Horizon\Contracts\Prism\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Prism\Component\ComponentResolverContract;
use Horizon\Contracts\Prism\Prism\Engine\PrismEngineContract;
use Horizon\Prism\Prism\Engine\TemplateContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TemplateContextTest extends TestCase
{
    private string $cachePath;
    private string $viewsPath;
    private PrismCompilerContract $compiler;
    private ComponentResolverContract $componentResolver;
    private PrismEngineContract $engine;

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
        $this->engine = $this->createMock(PrismEngineContract::class);
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

    public function test_evaluate_basic_template(): void
    {
        $context = new TemplateContext(
            $this->engine,
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath
        );

        $viewFile = $this->viewsPath . '/basic.php';
        file_put_contents($viewFile, 'Hello <?php echo $name; ?>');

        $output = $context->evaluate($viewFile, ['name' => 'World']);

        $this->assertSame('Hello World', $output);
    }

    public function test_blocks_and_slots(): void
    {
        $context = new TemplateContext(
            $this->engine,
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath
        );

        $context->startBlock('header');
        echo 'Header Content';
        $context->endBlock();

        $this->assertSame('Header Content', $context->slot('header'));
        $this->assertSame('', $context->slot('nonexistent'));
    }

    public function test_end_block_without_start_throws_exception(): void
    {
        $context = new TemplateContext(
            $this->engine,
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('endBlock() called without a matching startBlock().');

        $context->endBlock();
    }

    public function test_layout_inheritance(): void
    {
        $layoutView = $this->viewsPath . '/layout.prism.php';
        file_put_contents($layoutView, 'Layout: <?php echo $__prism->slot("content"); ?>');

        $this->compiler->method('compile')->willReturn($layoutView);

        $context = new TemplateContext(
            $this->engine,
            $this->componentResolver,
            $this->compiler,
            $this->viewsPath
        );

        $viewFile = $this->viewsPath . '/child.php';
        file_put_contents($viewFile, '<?php $__prism->layout("layout"); $__prism->startBlock("content"); ?>Child Content<?php $__prism->endBlock(); ?>');

        $output = $context->evaluate($viewFile, []);

        $this->assertSame('Layout: Child Content', $output);
    }
}
