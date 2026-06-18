<?php

declare(strict_types=1);

namespace Tests\Prism;

use Horizon\Contracts\Prism\Prism\Compiler\PrismCompilerContract;
use Horizon\Contracts\Prism\Prism\Engine\PrismEngineContract;
use Horizon\Prism\RenderedView;
use Horizon\Prism\ViewFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ViewFactoryTest extends TestCase
{
    private string $viewsPath;
    private PrismCompilerContract $compiler;
    private PrismEngineContract $engine;

    protected function setUp(): void
    {
        $this->viewsPath = sys_get_temp_dir() . '/prism_views_' . uniqid();

        if (!is_dir($this->viewsPath)) {
            mkdir($this->viewsPath, 0777, true);
        }

        $this->compiler = $this->createMock(PrismCompilerContract::class);
        $this->engine = $this->createMock(PrismEngineContract::class);
    }

    protected function tearDown(): void
    {
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

    public function test_make_resolves_compiles_and_renders_view(): void
    {
        $viewFile = $this->viewsPath . DIRECTORY_SEPARATOR . 'test.prism.php';
        file_put_contents($viewFile, 'Test');

        $compiledPath = '/tmp/compiled.php';

        $this->compiler->method('compile')->with($viewFile)->willReturn($compiledPath);
        $this->engine->method('render')->with($compiledPath, ['foo' => 'bar'])->willReturn('Rendered Content');

        $factory = new ViewFactory($this->compiler, $this->engine, $this->viewsPath);

        $view = $factory->make('test', ['foo' => 'bar']);

        $this->assertInstanceOf(RenderedView::class, $view);
        $this->assertSame('Rendered Content', $view->render());
        $this->assertSame('Rendered Content', (string) $view);
    }

    public function test_make_throws_exception_if_view_does_not_exist(): void
    {
        $factory = new ViewFactory($this->compiler, $this->engine, $this->viewsPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("View 'unknown' not found in path '{$this->viewsPath}'.");

        $factory->make('unknown');
    }

    public function test_exists_returns_true_if_view_found(): void
    {
        $viewFile = $this->viewsPath . '/existing.php';
        file_put_contents($viewFile, 'Exists');

        $factory = new ViewFactory($this->compiler, $this->engine, $this->viewsPath);

        $this->assertTrue($factory->exists('existing'));
        $this->assertFalse($factory->exists('missing'));
    }

    public function test_share_merges_global_data(): void
    {
        $viewFile = $this->viewsPath . '/test.html';
        file_put_contents($viewFile, 'HTML');

        $this->compiler->method('compile')->willReturn('compiled');
        $this->engine->expects($this->once())
            ->method('render')
            ->with('compiled', ['global' => 'shared', 'local' => 'data'])
            ->willReturn('');

        $factory = new ViewFactory($this->compiler, $this->engine, $this->viewsPath);
        $factory->share('global', 'shared');

        $factory->make('test', ['local' => 'data']);

        $this->assertSame(['global' => 'shared'], $factory->getShared());
    }

    public function test_share_array(): void
    {
        $factory = new ViewFactory($this->compiler, $this->engine, $this->viewsPath);
        $factory->share(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $factory->getShared());
    }
}
